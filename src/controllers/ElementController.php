<?php
/**
 * @link https://abm.at
 * @copyright Copyright (c) abm Feregyhazy & Simon GmbH
 */

namespace abmat\translationhelper\controllers;

use Craft;
use craft\errors\InvalidFieldException;
use craft\web\Controller;
use yii\base\InvalidConfigException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

class ElementController extends Controller
{

    /**
     * @param $originalSiteId
     * @param $elementUid
     * @param $elementId
     * @return string
     */
    protected function getOriginalTitleFieldValue($originalSiteId, $elementUid, $elementId): string
    {
        // Determine whether to search by ID or UID
        if (!empty($elementUid)) {
            $originalElement = Craft::$app->elements->getElementByUid($elementUid, null, $originalSiteId);
        } else {
            $originalElement = Craft::$app->elements->getElementById($elementId, null, $originalSiteId);
        }
        // Find entry and get its title if exists
        return $originalElement ? $originalElement->title : '';
    }

    /**
     * @param $originalSiteId
     * @param $handle
     * @param $elementId
     * @return string
     * @throws InvalidFieldException
     */
    protected function getOriginalFieldValue($originalSiteId, $handle, $elementId): string
    {
        $originalElement = Craft::$app->elements->getElementById($elementId, null, $originalSiteId);
        $value = '';
        if ($originalElement) {
            $value = $originalElement->getFieldValue($handle) ?? '';
        }
        return $value;
    }

    /**
     * @return void|Response
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     */
    public function actionFetch()
    {
        $this->requirePostRequest();

        $params = $this->request->getBodyParams();

        $isTitleField = isset($params['titlefield']) && filter_var($params['titlefield'], FILTER_VALIDATE_BOOLEAN) === true;
        $plainText = isset($params['plaintext']) && filter_var($params['plaintext'], FILTER_VALIDATE_BOOLEAN) === true;
        $originalSiteId = (int)$params['originalsiteid'];
        $elementId = (int)$params['elementid'];
        $elementUid = $params['elementuid'] ?? '';
        $handle = $params['handle'] ?? '';

        $originalSite = Craft::$app->sites->getSiteById($originalSiteId);

        $value = '';
        try {
            if ($isTitleField) {
                $value = $this->getOriginalTitleFieldValue($originalSiteId, $elementUid, $elementId);
            } else {
                $value = $this->getOriginalFieldValue($originalSiteId, $handle, $elementId);
            }
        } catch (\Exception $e) {

        }

        if (\Craft::$app->getRequest()->getAcceptsJson()) {
            return $this->asJson(
                [
                    'headline' => Craft::t('abm-translationhelper', "Original text from '{siteName}'", [
                            'siteName' => $originalSite->getName(),
                        ]) . ':',
                    'value' => $plainText ? htmlspecialchars($value) : $value,
                ]
            );
        }
    }
}
