<?php
class Styla_Connect_Model_Api2_Converter_Product_Image extends Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
{
    public function runConverter(Varien_Object $dataObject) {
        $objectImages = $this->getImages($dataObject);
        if(!$objectImages) {
            return;
        }
        
        if(count($objectImages) === 1) {
            $objectImages = reset($objectImages);
        }
        
        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $objectImages);
    }
}