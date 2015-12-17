<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Category_Image
 *
 */
class Styla_Connect_Model_Api2_Converter_Category_Image
    extends Styla_Connect_Model_Api2_Converter_Abstract
{
    public function runConverter(Varien_Object $dataObject) {
        $imageFile = $dataObject->getData('image');
        if(!$imageFile) {
            return;
        }
        
        $imageUrl = $url = Mage::getBaseUrl('media').'catalog/category/'.$imageFile;
        
        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $imageUrl);
    }
}