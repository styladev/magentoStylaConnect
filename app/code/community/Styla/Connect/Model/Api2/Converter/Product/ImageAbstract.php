<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
 */
abstract class Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
    extends Styla_Connect_Model_Api2_Converter_Abstract
{
    const REQUIREMENTS_TYPE = "image";
    /** @var Mage_Catalog_Model_Product_Media_Config */
    protected $_mediaConfig;
    protected $_placeholder;

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
        if (!$images) {
            $images = $this->_getGalleryAttributeImages($dataObject);
        }
        if (!$images) {
            return false;
        }

        $imagesWithUrl = array();
        foreach ($images as $image) {
            $imagesWithUrl[] = $this->getImageUrl($image);
        }

        return $imagesWithUrl;
    }

    /**
     * @param Varien_Object $dataObject
     * @return array|bool
     */
    public function getImageCaptions(Varien_Object $dataObject)
    {
        $captions = $this->_getCollectionImageCaptions($dataObject);
        if (!$captions) {
            $captions = $this->_getGalleryAttributeImages($dataObject, "label");
        }
        if (!$captions) {
            return false;
        }

        return $captions;
    }


    /**
     * Load product's media_gallery data
     *
     * @param Varien_Object $dataObject
     * @param string        $attributeToSelect
     * @return boolean|array
     */
    protected function _getGalleryAttributeImages(Varien_Object $dataObject, $attributeToSelect = "file")
    {
        $galleryData = $dataObject->getData('media_gallery');
        if (!isset($galleryData['images'])) {
            return false;
        }

        $images = array();
        foreach ($galleryData['images'] as $imageData) {
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
        $images = $dataObject->getData('all_images');

        return $images ? explode("|", $images) : false;
    }

    /**
     *
     * @param Varien_Object $dataObject
     * @return array|bool
     */
    protected function _getCollectionImageCaptions(Varien_Object $dataObject)
    {
        $captions = $dataObject->getData('all_image_labels');

        return $captions ? explode("|", $captions) : false;
    }

    /**
     * @param $imageFile
     * @return string
     */
    public function getImageUrl($imageFile)
    {
        if ($imageFile !== 'no_selection') {
            return $this->_getMediaConfig()
                ->getMediaUrl($imageFile);
        } else {
            return $this->_getPlaceholderImage();
        }
    }

    /**
     * @return string
     */
    protected function _getPlaceholderImage()
    {
        if (!$this->_placeholder) {
            $placeholder = Mage::getDesign()->getSkinUrl().'images/catalog/product/placeholder/image.jpg';
            //comment in when styla allows image urls to be started with a "//" instead of http|https
            //$this->_placeholder = preg_replace('/^https?:/', '', $placeholder);
            $this->_placeholder = $placeholder;
        }

        return $this->_placeholder;
    }

    /**
     * Retrieve media config
     *
     * @return Mage_Catalog_Model_Product_Media_Config
     */
    protected function _getMediaConfig()
    {
        if ($this->_mediaConfig === null) {
            $this->_mediaConfig = Mage::getSingleton('catalog/product_media_config');
        }

        return $this->_mediaConfig;
    }

    /**
     *  Load product images data
     *
     * @param mixed $dataCollection
     * @return $this
     */
    public function addRequirementsToDataCollection($dataCollection)
    {
        $mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute(
            'catalog_product',
            'media_gallery'
        )->getAttributeId();

        /** @var Varien_Db_Select $dataSelect */
        $dataSelect = $dataCollection->getSelect();

        $dataSelect->joinLeft(
            array('img' => 'catalog_product_entity_media_gallery'),
            sprintf('img.entity_id = e.entity_id AND img.attribute_id = %s', $mediaGalleryAttributeId),
            array()
        );
        $dataSelect->joinLeft(
            array('imginfo' => 'catalog_product_entity_media_gallery_value'),
            "imginfo.value_id = img.value_id",
            array()
        );

        $dataSelect->columns(
            array(
                'all_images'       => new Zend_Db_Expr(
                    "GROUP_CONCAT(img.value ORDER BY imginfo.position SEPARATOR '|')"
                ),
                'all_image_labels' => new Zend_Db_Expr("GROUP_CONCAT(IFNULL(imginfo.label, '') SEPARATOR '|')"),
            )
        );

        $dataSelect->group("e.entity_id");

        /*
         * we're also separately adding the main image, as it's the one supposed to be representing the whole product
         * and it can't be properly taken from the grouped images that we already have
         * 
         */
        $imageAttribute   = Mage::getSingleton('eav/config')->getCollectionAttribute('catalog_product', 'image');
        $imageTable       = $imageAttribute->getBackendTable();
        $imageAttributeId = $imageAttribute->getAttributeId();

        $dataSelect->joinLeft(
            array('mainimage' => $imageTable),
            'mainimage.attribute_id = '.$imageAttributeId." AND mainimage.entity_id = e.entity_id",
            array('value as main_image')
        );

        return $this;
    }
}