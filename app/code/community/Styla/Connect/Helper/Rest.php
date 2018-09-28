<?php

/**
 * Class Styla_Connect_Helper_Rest
 */
class Styla_Connect_Helper_Rest
{
    const QUERY_PARAM           = 'search';
    const EVENT_SEARCH_PRODUCTS = 'styla_connect_search_products';

    protected $_searchTerm;

    /**
     * Add search filter to the product collection.
     * A search will be based on the GET query param in the request.
     *
     * @param Styla_Connect_Model_Resource_Catalog_Product_Collection $collection
     * @param Mage_Core_Model_Store                                   $store
     */
    public function applySearchFilter(
        Styla_Connect_Model_Resource_Catalog_Product_Collection $collection,
        Mage_Core_Model_Store $store
    )
    {
        $searchTerm = $this->getSearchTerm();
        if (!$searchTerm) {
            return;
        }

        /** @var Varien_Object $transportObject */
        $transportObject = new Varien_Object();
        $transportObject->setIsProcessed(false);
        $transportObject->setProductIds(array());

        //dispatch the event. we'll expect for any custom search engine to fill the transport object's
        //product_ids array with entity_ids of all matching products, AND set the is_processed flag to TRUE,
        //so we know that the default search engine doesn't need to run, anymore.
        Mage::dispatchEvent(
            self::EVENT_SEARCH_PRODUCTS,
            array('transport_object' => $transportObject, 'store' => $store, 'search_term' => $searchTerm)
        );

        if ($transportObject->getIsProcessed()) {
            $filteredProductIds = $transportObject->getProductIds();
        } else {
            //no custom search engine responded to this event, so we'll apply the default filtering
            $filteredProductIds = $this->_searchDefaultFulltext($collection, $store);
        }

        $filteredProductIds = is_array($filteredProductIds) ? $filteredProductIds : array();
        if (filteredProductIds) {            
            $this->_applySearchFilterOnProductCollection($collection, $filteredProductIds);
        }
    }

    /**
     * Run the default Fulltext CatalogSearch search.
     *
     * @param Styla_Connect_Model_Resource_Catalog_Product_Collection $collection
     * @param Mage_Core_Model_Store                                   $store
     * @return array
     */
    protected function _searchDefaultFulltext(
        Styla_Connect_Model_Resource_Catalog_Product_Collection $collection,
        Mage_Core_Model_Store $store
    )
    {
        $this->getSearchTerm(); //needed to initialize the catalogsearch query

        //start the store emulation, as some search engines will try to get the current_store:
        $appEmulation           = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());

        $query = Mage::helper('catalogsearch')->getQuery();
        $query->setStoreId($store->getId());
        if (!$query->getId()) {
            $query->prepare(); //we may need to pre-save the query
        }

        $searchCollection = Mage::getResourceModel('catalogsearch/fulltext_collection');
        $searchCollection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addSearchFilter($query->getQueryText())
            ->setStore($store)
            ->addStoreFilter();

        if ($this->isMagentoUsesNewestFullTextFeature()) {
            $searchCollection->setOrder($searchCollection::RELEVANCE_ORDER_NAME, $searchCollection::SORT_ORDER_DESC);
            $productIds = $searchCollection->getFoundIds();
        } else {
            $productIds = $searchCollection->getAllIds();
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return is_array($productIds) ? $productIds : array();
    }

    /**
     * Filter the product collection by given entity_ids
     *
     * @param Styla_Connect_Model_Resource_Catalog_Product_Collection $collection
     * @param array                                                   $entityIds
     */
    protected function _applySearchFilterOnProductCollection(
        Styla_Connect_Model_Resource_Catalog_Product_Collection $collection,
        array $entityIds
    )
    {
        $collection->addFieldToFilter('entity_id', array('in' => $entityIds));
        $collection->getSelect()->order(
            new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $entityIds).')')
        );
    }

    /**
     * Get the requested search term
     *
     * @return string|bool
     */
    public function getSearchTerm()
    {
        if ($this->_searchTerm === null) {
            $searchTerm = Mage::app()->getRequest()->getParam('search');
            if ($searchTerm) {
                //copy the search term to magento's default query param, so that the default search engines may work with it
                Mage::app()->getRequest()->setParam(Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME, $searchTerm);

                /* @var $stringHelper Mage_Core_Helper_String */
                $stringHelper = Mage::helper('core/string');
                $searchTerm   = is_array($searchTerm) ? '' : $stringHelper->cleanString(trim($searchTerm));

                $this->_searchTerm = $searchTerm;
            } else {
                $this->_searchTerm = false;
            }
        }

        return $this->_searchTerm;
    }

    /**
     * @return bool
     */
    public function isMagentoUsesNewestFullTextFeature()
    {
        $uses = false;
        $edition = Mage::getEdition();
        $version = Mage::getVersion();

        if ($edition == Mage::EDITION_COMMUNITY
            && version_compare($version, '1.9.3', '>=' )) {
            $uses = true;
        }

        if ($edition == Mage::EDITION_ENTERPRISE
            && version_compare($version, '1.14.3', '>=' )) {
            $uses = true;
        }

        return $uses;
    }
}
