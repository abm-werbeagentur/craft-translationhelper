<?php
/**
 * @link https://abm.at
 * @copyright Copyright (c) abm Feregyhazy & Simon GmbH
*/

namespace abmat\translationhelper\controllers;

use Craft;
use craft\db\Table;
use craft\web\Controller;
use craft\web\Response;
use yii\web\BadRequestHttpException;
use craft\services\Elements;
use craft\helpers\Db;
use craft\helpers\ArrayHelper;

class ElementController extends Controller {
	/**
	 * @throws ForbiddenHttpException
	 */
	public function actionFetch ()
	{
		$this->requirePostRequest();
		
		$params = $this->request->getBodyParams();
		$original_element = Craft::$app->elements->getElementById($params['elementid'], null, $params['originalsiteid']);
		$value = '';
		if($original_element) {
			$value = $original_element->getFieldValue($params['handle']);
		}

		/*
		$elementContext = $params['elementcontext'];
		if($elementContext == 'global') {
			$original_element = Craft::$app->elements->getElementById($params['elementid'], null, $params['originalsiteid']);
			$value = $original_element->getFieldValue($params['handle']);
		}
		else {
			$contextArray = explode(":", $elementContext);
			switch($contextArray[0]) {
				case 'matrixBlockType': {
					$original_element = Craft::$app->elements->getElementById($params['elementid'], null, $params['originalsiteid']);
					$value = $original_element->getFieldValue($params['handle']);
				}break;

				default: {

				}break;
			}
		}
		*/

		
		$request = Craft::$app->getRequest();
		if ($request->getAcceptsJson()) {
			return $this->asJson(
				[
					'value' => strip_tags($value)
				]
			);
		}
		
	}
}