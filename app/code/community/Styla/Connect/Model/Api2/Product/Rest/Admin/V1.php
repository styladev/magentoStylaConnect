<?php
class Styla_Connect_Model_Api2_Product_Rest_Admin_v1 extends Mage_Catalog_Model_Api2_Product_Rest_Admin_V1
{
    /**
     * Retrieve product data
     *
     * @return array
     */
    protected function _retrieve()
    {
        $product = $this->_getProduct();

        $this->_prepareProductForResponse($product);
        $this->_getResponseConfig()->prepareStylaApiResponse($product, "product");

        return $product->getData();
    }
    
    /**
     * Retrieve list of products
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $products = $this->_getProductCollection();
        
        $this->_getResponseConfig()->prepareStylaApiResponse($products, "product");
        
        return $this->_getCollectionData($products);
    }
    
    /**
     * 
     * @param Varien_Data_Collection $collection
     * @return array
     */
    protected function _getCollectionData(Varien_Data_Collection $collection)
    {
        $data = array();
        foreach($collection as $item) {
            $data[] = $item->getData();
        }
        
        return $data;
    }
    
    /**
     * Retrieve list of products
     *
     * @return array
     */
    protected function _getProductCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('styla_connect/catalog_product_collection');
        $store = $this->_getStore();
        $collection->setStoreId($store->getId());
        $collection->addAttributeToSelect(array_keys(
            $this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
        ));
        $this->_applyCategoryFilter($collection);
        $this->_applySearchFilter($collection);
        $this->_applyCollectionModifiers($collection);
        
        $this->_loadImages($collection);
        
        return $collection;
    }
    
    /**
     * Add fulltext search term to the loaded collection filters
     * 
     * @param Styla_Connect_Model_Resource_Catalog_Product_Collection $collection
     */
    protected function _applySearchFilter(Styla_Connect_Model_Resource_Catalog_Product_Collection $collection)
    {
        $searchTerm = $this->getRequest()->getParam('search');
        if($searchTerm) {
            $collection->addFulltextSearchTerm($searchTerm);
        }
    }
    
    /**
     * Add catalog images to the loaded product collection
     * 
     * @param Styla_Connect_Model_Resource_Catalog_Product_Collection $collection
     * @return null
     */
    protected function _loadImages(Styla_Connect_Model_Resource_Catalog_Product_Collection $collection)
    {
        $product = $collection->getFirstItem();
        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);
        $mediaGallery = isset($attributes['media_gallery']) ? $attributes['media_gallery'] : null;
        if(!$mediaGallery) {
            return;
        }
        
        $attributeBackend = $mediaGallery->getBackend();
        foreach($collection as $product) {
            $attributeBackend->afterLoad($product);
        }
    }
    
    /**
     * 
     * @return Styla_Connect_Model_Api2_ResponseConfig
     */
    protected function _getResponseConfig()
    {
        return Mage::getSingleton('styla_connect/api2_responseConfig');
    }
}