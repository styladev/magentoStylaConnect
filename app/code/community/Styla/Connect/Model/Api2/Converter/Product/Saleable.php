<?php
class Styla_Connect_Model_Api2_Converter_Product_Saleable extends Styla_Connect_Model_Api2_Converter_Abstract
{
    public function runConverter(Varien_Object $dataObject)
    {
        $dataObject->unsetData('is_salable');
        $value = ($dataObject->isSaleable());
        
        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $value);
    }
}