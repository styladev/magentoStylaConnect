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
        $productCollection = $this->_getProductCollection();

        $this->_addPagingHeaderData($productCollection);
        $this->_getResponseConfig()->prepareStylaApiResponse($productCollection, "product");


        return $this->_getCollectionData($productCollection);
    }

    protected function _addPagingHeaderData(Varien_Data_Collection $collection)
    {
        $request = $this->getRequest();

        $links = array();

        $totalCount = $collection->getSize();
        $pageSize   = $request->getPageSize();

        $totalPageCount = ceil($totalCount / $pageSize);
        $currentPage    = $collection->getCurPage();

        if ($currentPage === null) {
            $currentPage = 1;
        }

        if ($currentPage > 1) {
            $links[] = $this->_getPageLink(1, 'first');
            $links[] = $this->_getPageLink($currentPage - 1, 'prev');
        }

        if ($currentPage < $totalPageCount) {
            $links[] = $this->_getPageLink($currentPage + 1, 'next');
            $links[] = $this->_getPageLink($totalPageCount, 'last');
        }

        $this->getResponse()
            ->setHeader('Link', implode(", ", $links))
            ->setHeader('X-Total-Count', $totalCount)
            ->setHeader('X-Total-Pages', $totalPageCount)
            ->setHeader('X-Current-Page', $currentPage);
    }

    protected function _getPageLink($page, $rel)
    {
        return sprintf(
            '<%s>; rel="%s"',
            $this->_getPagingUrl($page),
            $rel
        );
    }

    protected function _getPagingUrl($page)
    {
        $request         = $this->getRequest();
        $queryParameters = $this
            ->getRequest()
            ->getQuery();

        //unset the "type" parameter that is not needed
        unset($queryParameters['type']);

        //try to force ssl for paging urls
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true) . 'rest/api';

        $baseUrl .= $this->getRequest()->getPathInfo();

        $queryParameters[$request::QUERY_PARAM_PAGE_NUM] = $page;

        return $baseUrl . '?' . http_build_query($queryParameters);
    }

    /**
     *
     * @param Varien_Data_Collection $collection
     * @return array
     */
    protected function _getCollectionData(Varien_Data_Collection $collection)
    {

        return array_values($collection->toArray());
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
        $store      = $this->_getStore();
        
        $collection->setStoreId($store->getId());
        $collection->addAttributeToSelect(array_keys(
            $this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
        ));

        $this->_applyCategoryFilter($collection);
        $this->_applySearchFilter($collection);
        $this->_applyCollectionModifiers($collection);
        
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
        if ($searchTerm) {
            $collection->addFulltextSearchTerm($searchTerm);
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