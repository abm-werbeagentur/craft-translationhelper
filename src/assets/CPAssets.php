<?php
/**
 * @link https://abm.at
 * @copyright Copyright (c) abm Feregyhazy & Simon GmbH
*/

namespace abmat\translationhelper\assets;

use craft\web\AssetBundle;

class CPAssets extends AssetBundle {

	public function init(): void
    {
        $this->sourcePath = __DIR__."/ressources/dist";

        $this->js = [
            'js/abm-translationhelper.js',
        ];

        $this->css = [
            'css/abm-translationhelper.css',
        ];

        parent::init();
    }
}