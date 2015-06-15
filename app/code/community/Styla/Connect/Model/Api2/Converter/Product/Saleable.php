<?php
class Styla_Connect_Model_Api2_Converter_Product_Saleable extends Styla_Connect_Model_Api2_Converter_Abstract
{
    public function runConverter(Varien_Object $dataObject)
    {
        /**
         * Since products are loaded in the context of admin store, and there's no stock for admin -
         * by default the value of is_salable is set to null and is wrong. To make magento reconsider,
         * and to allow isSaleable() to work correctly, we need to get rid of this wrong value, first.
         */
        $dataObject->unsetData('is_salable');        
        
        $value = ($dataObject->isSaleable());
        
        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $value);
    }
}