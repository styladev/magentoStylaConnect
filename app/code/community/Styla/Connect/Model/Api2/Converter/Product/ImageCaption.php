<?php
class Styla_Connect_Model_Api2_Converter_Product_ImageCaption extends Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
{
    public function runConverter(Varien_Object $dataObject) {
        $objectImages = $this->getImages($dataObject);
        if(!$objectImages) {
            return;
        }
        
        $imageCaptions = array();
        foreach($objectImages as $objectImage) {
            $imageCaptions[] = isset($objectImage['label_default']) ? $objectImage['label_default'] : "";
        }
        
        if(count($imageCaptions) === 1) {
            $imageCaptions = reset($imageCaptions);
        }
        
        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $imageCaptions);
    }
}