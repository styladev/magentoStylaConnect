<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_Url
 */
class Styla_Connect_Model_Api2_Converter_Product_Url
    extends Styla_Connect_Model_Api2_Converter_Abstract
{
    /**
     * @param Varien_Object $dataObject
     */
    public function runConverter(Varien_Object $dataObject)
    {
        /** @var Mage_Catalog_Model_Product $dataObject */
        
        $stylaField = $this->getStylaField();


        $productUrl = $dataObject->getProductUrl();
        if ($this->_useRelativeUrls()) {
            $productUrl = str_replace(Mage::getBaseUrl(), "/", $productUrl);
        }

        $dataObject->setData($stylaField, $productUrl);
    }

    /**
     * Should only return the relative part of the urls
     *
     * @return bool
     */
    protected function _useRelativeUrls()
    {
        return Mage::helper('styla_connect')->isUsingRelativeProductUrls();
    }
}
