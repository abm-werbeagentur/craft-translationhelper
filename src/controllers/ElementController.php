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

		$originalSite = Craft::$app->sites->getSiteById($params['originalsiteid']);
		$originalElement = Craft::$app->elements->getElementById($params['elementid'], null, $params['originalsiteid']);
		$value = '';
		if($originalElement) {
			$value = $originalElement->getFieldValue($params['handle']);
		}

		/*
		$elementContext = $params['elementcontext'];
		if($elementContext == 'global') {
			$originalElement = Craft::$app->elements->getElementById($params['elementid'], null, $params['originalsiteid']);
			$value = $originalElement->getFieldValue($params['handle']);
		}
		else {
			$contextArray = explode(":", $elementContext);
			switch($contextArray[0]) {
				case 'matrixBlockType': {
					$originalElement = Craft::$app->elements->getElementById($params['elementid'], null, $params['originalsiteid']);
					$value = $originalElement->getFieldValue($params['handle']);
				}break;

				default: {

				}break;
			}
		}
		*/

		
		if (\Craft::$app->getRequest()->getAcceptsJson()) {
			return $this->asJson(
				[
					'headline' => Craft::t('abm-translationhelper', "Original text from '{siteName}':", [
						'siteName' => $originalSite->getName()
					]),
					'value' => $value
				]
			);
		}
		
	}
}