<?php

namespace abmat\translationhelper;

use abmat\translationhelper\assets\CPAssets;
use abmat\translationhelper\models\Settings;
use Craft;
use craft\base\Field;
use craft\base\Model;

use craft\base\Plugin;

use craft\enums\PropagationMethod;
use craft\errors\SiteNotFoundException;
use craft\events\DefineFieldHtmlEvent;
use craft\events\RegisterUrlRulesEvent;

use craft\events\TemplateEvent;
use craft\fields\PlainText;

use craft\helpers\Cp;
use craft\helpers\Json;
use craft\helpers\StringHelper;

use craft\models\Site;
use craft\web\UrlManager;

use craft\web\View;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Translation Helper plugin
 *
 * @method static TranslationHelper getInstance()
 * @method Settings getSettings()
 * @author abm Feregyhazy & Simon GmbH <developer@abm.at>
 * @copyright abm Feregyhazy & Simon GmbH
 * @license https://craftcms.github.io/license/ Craft License
 */
class TranslationHelper extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public bool $hasCpSection = false;

    /**
     * @return array[]
     */
    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    /**
     * @return void
     */
    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function () {
            $this->attachEventHandlers();
        });
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerCpRoutes();
            $this->_registerTranslations();
        }
    }

    /**
     * @return Model|null
     * @throws InvalidConfigException
     */
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    /**
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function settingsHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(CPAssets::class);
        return Craft::$app->view->renderTemplate('abm-translationhelper/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * @param $currentSiteId
     * @return Site|null
     * @throws SiteNotFoundException
     */
    private static function getOriginalSite($currentSiteId): ?Site
    {
        $settings = TranslationHelper::getInstance()->getSettings();
        if (isset($settings->selectedSiteGroups[Craft::$app->sites->getSiteById($currentSiteId)->groupId][$currentSiteId])) {
            return Craft::$app->sites->getSiteById($settings->selectedSiteGroups[Craft::$app->sites->getSiteById($currentSiteId)->groupId][$currentSiteId]);
        }
        return Craft::$app->sites->getPrimarySite();
    }

    /**
     * @return void
     */
    private function attachEventHandlers(): void
    {

        // Register the event handler for the View::EVENT_AFTER_RENDER_TEMPLATE event
        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_TEMPLATE,
            function (TemplateEvent $event) {

                if (!Craft::$app->getRequest()->getIsCpRequest() || !Cp::requestedSite()) {
                    return;
                }

                /** @var View $sender */
                $sender = $event->sender;

                $currentSiteId = Cp::requestedSite()->getId();
                $site = static::getOriginalSite($currentSiteId);

                if (!$site || $site->id == $currentSiteId) {
                    return;
                }

                // title field template
                if (!isset($event->variables['id']) || $event->variables['id'] !== 'title') {
                    return;
                }

                try {

                    $elementUid = null;

                    // convert the string event->sender->namespace string to an array e.g. "fields[title]" to ["fields", "title"]
                    if (is_string($sender->namespace)) {
                        $parts = explode('[', str_replace(']', '', $sender->namespace));
                        // set $namespace to the last part of the array if it starts with "uid:"
                        if (count($parts) > 0 && str_starts_with($parts[count($parts) - 1], 'uid:')) {
                            $elementUid = explode(':', $parts[count($parts) - 1])[1] ?? null;
                        }
                    }

                    // action request
                    if (Craft::$app->getRequest()->getIsActionRequest()) {
                        $elementId = Craft::$app->getRequest()->getParam('elementId') ?? null;
                    } else {
                        $vars = Craft::$app->getUrlManager()->parseRequest(Craft::$app->getRequest())[1] ?? [];
                        $elementId = $vars['elementId'] ?? null;
                    }

                    if ($elementId || $elementUid) {
                        Craft::$app->getView()->registerAssetBundle(CPAssets::class);
                        $event->output .= "\n" . Craft::$app->view->renderTemplate('abm-translationhelper/_button.twig',
                                ['isTitleField' => true, 'elementUid' => $elementUid, 'elementId' => $elementId, 'plainText' => true, 'original_site' => $site, 'hash' => StringHelper::UUID()]);
                    }

                } catch (\Exception $e) {
                    return;
                }

            }
        );

        Event::on(
            Field::class,
            Field::EVENT_DEFINE_INPUT_HTML,
            static function (DefineFieldHtmlEvent $event) {

                $currentSiteId = $event->element->siteId;

                switch ($event->sender->translationMethod) {
                    case Field::TRANSLATION_METHOD_NONE:
                    {
                        return;
                    }
                    default:
                        {
                            $site = static::getOriginalSite($currentSiteId);
                        }
                        break;
                }

                if (!$site || $site->id == $currentSiteId) {
                    return;
                }

                /* check if element is part of matrixBlock ... if yes check propagationMethod of matrixBlock */
                $elementContext = $event->element->getFieldContext();
                $contextArray = explode(":", $elementContext);
                switch ($contextArray[0]) {
                    case 'matrixBlockType':
                        {
                            if (isset($event->element->owner) && is_object($event->element->owner) && get_class($event->element->owner) == 'verbb\vizy\elements\Block') {
                                return;
                            }
                            if ($event->element->uid) {
                                $matrixElement = Craft::$app->elements->getElementByUid($event->element->uid, null, $currentSiteId);
                                if ($matrixElement) {
                                    switch (Craft::$app->fields->getFieldById($matrixElement->fieldId)->propagationMethod) {
                                        case PropagationMethod::None:
                                        {
                                            return;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }

                if (get_class($event->element) == 'verbb\vizy\elements\Block') {
                    return;
                }

                $plainText = get_class($event->sender) === PlainText::class;

                switch (get_class($event->sender)) {
                    case 'abmat\tinymce\Field':
                    case 'craft\redactor\Field':
                    case 'craft\ckeditor\Field':
                    case PlainText::class:
                        {
                        }
                        break;

                    default:
                    {
                        //asset
                        return;
                    }
                }

                Craft::$app->getView()->registerAssetBundle(CPAssets::class);
                $event->html = $event->html . "\n" . Craft::$app->view->renderTemplate('abm-translationhelper/_button.twig',
                        ['event' => $event, 'original_site' => $site, 'plainText' => $plainText, 'hash' => StringHelper::UUID()]);

            }
        );
    }

    /**
     * @return void
     */
    private function _registerCpRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event): void {
                $event->rules['abm-translationhelper/element/fetch'] = 'abm-translationhelper/element/fetch';
            }
        );
    }

    /**
     * @return void
     */
    private function _registerTranslations(): void
    {
        $view = Craft::$app->getView();
        $customTranslations = [
            'copy_to_clipbard' => Craft::t('abm-translationhelper', 'Copy to clipboard'),
            'copied_to_clipboard' => Craft::t('abm-translationhelper', 'Copied to clipboard'),
            'copied' => Craft::t('abm-translationhelper', 'Copied'),
            'close' => Craft::t('abm-translationhelper', 'Close'),
        ];

        $view->registerJs('
			if(typeof translations === "undefined") {translations = new Object();}
			translations.abmtranslationhelper = ' . Json::encode($customTranslations) . ';
		');
    }
}
