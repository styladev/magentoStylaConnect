<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_Url
 */
class Styla_Connect_Model_Api2_Converter_Product_Url extends Styla_Connect_Model_Api2_Converter_Abstract
{
    /**
     * @param Varien_Object $dataObject
     */
    public function runConverter(Varien_Object $dataObject) {
        $stylaField = $this->getStylaField();
        
        $environmentInfo = $this->_emulateFrontend();
        
        //we need to temporarily set the same store id to the object, or else we won't get a rewritten url
        $oldId = $dataObject->getStoreId();
        
        $dataObject->setStoreId($this->_getDefaultStoreViewId());
        $productUrl = $dataObject->getProductUrl();
        $dataObject->setStoreId($oldId);
        
        $this->_stopEmulation($environmentInfo);
        
        $dataObject->setData($stylaField, $productUrl);
    }
}