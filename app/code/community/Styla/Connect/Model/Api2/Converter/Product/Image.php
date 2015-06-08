<?php
class Styla_Connect_Model_Api2_Converter_Product_Image extends Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
{
    public function runConverter(Varien_Object $dataObject) {
        $objectImages = $this->getImages($dataObject);
        if(!$objectImages) {
            return;
        }
        
        $images = array();
        foreach($objectImages as $objectImage) {
            $images[] = $this->getImageUrl($objectImage['file']);
        }
        
        if(count($images) === 1) {
            $images = reset($images);
        }
        
        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $images);
    }
}