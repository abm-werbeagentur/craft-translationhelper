<?php
/**
 * @link https://abm.at
 * @copyright Copyright (c) abm Feregyhazy & Simon GmbH
*/

namespace abmat\translationhelper\controllers;

use craft\web\Controller;


class OverviewController extends Controller {
	
	public function actionIndex ()
	{
		return $this->renderTemplate('abm-translationhelper/_overview/index', []);
	}
}