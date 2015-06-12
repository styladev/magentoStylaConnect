<?php
abstract class Styla_Connect_Model_Api2_Converter_Product_ImageAbstract extends Styla_Connect_Model_Api2_Converter_Abstract
{
    const REQUIREMENTS_TYPE = "image";
    
    /**
     * Get product images. Uses a joined image data for collections, or
     * a media_gallery attribute for single entities.
     * 
     * @param Varien_Object $dataObject
     * @return boolean|array
     */
    public function getImages(Varien_Object $dataObject)
    {
        $images = $this->_getCollectionImages($dataObject);
        if(!$images) {
            $images = $this->_getGalleryAttributeImages($dataObject);
        }
        if(!$images) {
            return false;
        }
        
        $imageLimit = $this->getImageLimit();
        if($imageLimit) {
            $images = array_slice($images, 0, $imageLimit);
        }
        
        $imagesWithUrl = array();
        foreach($images as $image) {
            $imagesWithUrl[] = $this->getImageUrl($image);
        }    
        return $imagesWithUrl;
    }
    
    /**
     * Load product's media_gallery data
     * 
     * @param Varien_Object $dataObject
     * @param string $attributeToSelect
     * @return boolean|array
     */
    protected function _getGalleryAttributeImages(Varien_Object $dataObject, $attributeToSelect = "file")
    {
        $galleryData = $dataObject->getData('media_gallery');
        if(!isset($galleryData['images'])) {
            return false;
        }
        
        $images = array();
        foreach($galleryData['images'] as $imageData) {
            $images[] = $imageData[$attributeToSelect];
        }
        
        return $images;
    }
    
    /**
     * 
     * @param Varien_Object $dataObject
     * @return array|bool
     */
    protected function _getCollectionImages(Varien_Object $dataObject)
    {
        $images = $dataObject->getAllImages();
        return $images ? explode("|", $images) : false;
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
    
    /**
     *  Load product images data
     * 
     * @param mixed $dataCollection
     */
    public function addRequirementsToDataCollection($dataCollection)
    {
        $mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'media_gallery')->getAttributeId();
        
        $dataSelect = $dataCollection->getSelect();
        
        $dataSelect->joinLeft(array('img' => 'catalog_product_entity_media_gallery'), "img.entity_id = e.entity_id", array());
        $dataSelect->joinLeft(array('imginfo' => 'catalog_product_entity_media_gallery_value'), "imginfo.value_id = img.value_id", array());
        
        $dataSelect->columns(
                array(
                    'all_images'        => new Zend_Db_Expr("GROUP_CONCAT(img.value SEPARATOR '|')"),
                    'all_images_data'   => new Zend_Db_Expr("GROUP_CONCAT(IFNULL(imginfo.label, '') SEPARATOR '|')")
                ));
        
        $dataSelect->where("img.attribute_id = ?", $mediaGalleryAttributeId);
        
        $dataSelect->group("e.entity_id");
    }
}