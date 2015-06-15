<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Const
 */
class Styla_Connect_Model_Api2_Converter_Const extends Styla_Connect_Model_Api2_Converter_Abstract
{
    public function runConverter(Varien_Object $dataObject) {
        $value = $this->getArgument('value');
        $stylaField = $this->getStylaField();
        
        $dataObject->setData($stylaField, $value);
    }
}