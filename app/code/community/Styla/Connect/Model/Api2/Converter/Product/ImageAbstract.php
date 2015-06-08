<?php
abstract class Styla_Connect_Model_Api2_Converter_Product_ImageAbstract extends Styla_Connect_Model_Api2_Converter_Abstract
{
    public function getImages(Varien_Object $dataObject)
    {
        $images = $this->_getImages($dataObject);
        if(!$images) {
            return;
        }
        
        $imageLimit = $this->getImageLimit();
        if($imageLimit) {
            $images = array_slice($images, 0, $imageLimit);
        }
        
        return $images;
    }
    
    protected function _getImages(Varien_Object $dataObject)
    {
        $galleryData = $dataObject->getData('media_gallery');
        if (!isset($galleryData['images']) || !is_array($galleryData['images'])) {
            return false;
        }
        
        return $galleryData['images'];
    }
    
    public function getImageLimit()
    {
        return (int)$this->getArgument('image_limit');
    }
    
    public function getImageUrl($imageFile)
    {
        return $this->_getMediaConfig()->getMediaUrl($imageFile);
    }
    
    /**
     * Retrieve media config
     *
     * @return Mage_Catalog_Model_Product_Media_Config
     */
    protected function _getMediaConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }
}