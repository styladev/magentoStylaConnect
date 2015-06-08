<?php
class Styla_Connect_Model_Api2_Converter_Simple extends Styla_Connect_Model_Api2_Converter_Abstract
{
    public function runConverter(Varien_Object $dataObject) {
        $stylaField = $this->getStylaField();
        $magentoField = $this->getMagentoField();
        
        $magentoValue = $dataObject->getData($magentoField);
        
        $dataObject->setData($stylaField, $magentoValue);
    }
}