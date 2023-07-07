<?php
/**
 * @link https://abm.at
 * @copyright Copyright (c) abm Feregyhazy & Simon GmbH
*/

namespace abmat\translationhelper\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use yii\web\BadRequestHttpException;
use craft\services\Elements;

class ElementController extends Controller {
	/**
	 * @throws ForbiddenHttpException
	 */
	public function actionFetch ()
	{
		$this->requirePostRequest();
		
		$params = $this->request->getBodyParams();

		$original_element = Craft::$app->elements->getElementById($params['elementid'], null, $params['originalsiteid']);

		$request = Craft::$app->getRequest();
		if ($request->getAcceptsJson()) {
			return $this->asJson(
				[
					'value' => $original_element->getFieldValue($params['handle'])
				]
			);
		}
	}
}