<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_Saleable
 */
class Styla_Connect_Model_Api2_Converter_Product_Saleable
    extends Styla_Connect_Model_Api2_Converter_Abstract
{
    /**
     * @param Varien_Object $dataObject
     */
    public function runConverter(Varien_Object $dataObject)
    {
        $value = ($dataObject->isSaleable());

        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $value);
    }
}