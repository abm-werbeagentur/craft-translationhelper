<?php

namespace abmat\translationhelper;

use Craft;
use abmat\translationhelper\models\Settings;
use craft\base\Model;
use craft\base\Plugin;

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
            // ...
        });
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
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
    }
}
