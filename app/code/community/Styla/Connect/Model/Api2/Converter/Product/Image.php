<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_Image
 */
class Styla_Connect_Model_Api2_Converter_Product_Image extends Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
{
    /**
     * @param Varien_Object $dataObject
     */
    public function runConverter(Varien_Object $dataObject) {
        $objectImages = $this->getImages($dataObject);
        if(!$objectImages) {
            return;
        }

        //single image if you want more use the image collection
        $objectImages = reset($objectImages);
        
        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $objectImages);
    }
}