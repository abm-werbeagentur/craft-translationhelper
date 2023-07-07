<?php

namespace abmat\translationhelper;

use Craft;
use abmat\translationhelper\models\Settings;
use craft\base\Model;
use craft\base\Plugin;
use yii\base\Event;
use craft\base\Field;
use craft\events\DefineFieldHtmlEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\StringHelper;
use craft\web\UrlManager;
use craft\models\Site;

use abmat\translationhelper\assets\CPAssets;

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

	public static function config(): array
	{
		return [
			'components' => [
					// Define component configs here...
			],
		];
	}

	public function init(): void
	{
		parent::init();

		// Defer most setup tasks until Craft is fully initialized
		Craft::$app->onInit(function() {
			$this->attachEventHandlers();
		});
		if (Craft::$app->getRequest()->getIsCpRequest()) {
			$this->_registerCpRoutes();
		}
	}

	
	protected function createSettingsModel(): ?Model
	{
		return Craft::createObject(Settings::class);
	}

	protected function settingsHtml(): ?string
	{
		return Craft::$app->view->renderTemplate('abm-translationhelper/_settings.twig', [
			'plugin' => $this,
			'settings' => $this->getSettings(),
		]);
	}
	

	private function attachEventHandlers(): void
	{
		Event::on(
			Field::class,
			Field::EVENT_DEFINE_INPUT_HTML,
			static function (DefineFieldHtmlEvent $event) {
				/** @var SettingsModel $settings */
				
				$settings = TranslationHelper::getInstance()->getSettings();
				$currentSiteId = $event->element->siteId;

				//echo "<br /><h1>begin sender</h1><pre>";print_r($event->sender);

				$showTranslationHelperButton = true;
				switch($event->sender->translationMethod) {
					case Field::TRANSLATION_METHOD_NONE: {
						$showTranslationHelperButton = false;
						return;
					}break;

					case Field::TRANSLATION_METHOD_SITE: {
						if(isset($settings->selectedSiteGroups[Craft::$app->sites->getSiteById($currentSiteId)->groupId])) {
							$site = Craft::$app->sites->getSiteById($settings->selectedSiteGroups[Craft::$app->sites->getSiteById($currentSiteId)->groupId]);
						} else {
							$site = Craft::$app->sites->getPrimarySite();
						}
					}break;

					case Field::TRANSLATION_METHOD_SITE_GROUP: {
						if(isset($settings->selectedSiteGroups[Craft::$app->sites->getSiteById($currentSiteId)->groupId])) {
							$site = Craft::$app->sites->getSiteById($settings->selectedSiteGroups[Craft::$app->sites->getSiteById($currentSiteId)->groupId]);
						} else {
							$site = Craft::$app->sites->getPrimarySite();
						}
					}break;

					case FIELD::TRANSLATION_METHOD_LANGUAGE: {
						$site = Craft::$app->sites->getPrimarySite();
					}break;

					case FIELD::TRANSLATION_METHOD_CUSTOM: {
						$site = Craft::$app->sites->getPrimarySite();
					}break;

					default: {
						$site = Craft::$app->sites->getPrimarySite();
					}break;
				}

				if($site->id ==  $currentSiteId) {
					return;
				}




				switch(get_class($event->sender)) {
					case 'craft\fields\Lightswitch': {
						//Lightswitch
					}break;

					case 'abmat\tinymce\Field': {
						//TinyMCE
						//echo "<pre>";print_r($event->sender);echo "</pre>";
					}break;
					
					case 'craft\fields\Matrix':
					case 'craft\fields\Assets': {
						//asset
						$showTranslationHelperButton = false;
					}break;

					default: {
						//echo get_class($event->sender) . "<br />";
					}break;
				}

				if($showTranslationHelperButton) {
					Craft::$app->getView()->registerAssetBundle(CPAssets::class);
					
					$event->html = "\n" . Craft::$app->view->renderTemplate('abm-translationhelper/_button.twig',
						[ 'event' => $event, 'original_site' => $site, 'hash' => StringHelper::UUID()] ) . "\n" . $event->html;
				}
			}
		);
	}

	private function _registerCpRoutes (): void
	{
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function(RegisterUrlRulesEvent $event): void {
				$event->rules['abm-translationhelper/element/fetch'] = 'abm-translationhelper/element/fetch';
			}
		);
	}
}
