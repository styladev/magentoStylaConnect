<?php
class Styla_Connect_Model_Api2_Converter_Product_ImageCaption extends Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
{
    public function runConverter(Varien_Object $dataObject) {
        $imageCaptions = $this->_getImageCaptions($dataObject);
        if(!$imageCaptions) {
            $imageCaptions = $this->_getGalleryAttributeImages($dataObject, "label");
        }
        if(!$imageCaptions) {
            return;
        }
        
        //in this version of styla api, we actually only care about the first image's caption, so:
        $imageCaptions = reset($imageCaptions);
        
        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $imageCaptions);
    }
    
    protected function _getImageCaptions(Varien_Object $dataObject)
    {
        $captions = $dataObject->getAllImagesData();
        
        return $captions ? explode("|", $captions) : false;
    }
}