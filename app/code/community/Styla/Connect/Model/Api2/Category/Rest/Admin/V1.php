<?php

/**
 * Class Styla_Connect_Model_Api2_Category_Rest_Admin_V1
 *
 */
class Styla_Connect_Model_Api2_Category_Rest_Admin_V1
    extends Mage_Catalog_Model_Api2_Product_Category_Rest_Admin_V1
{
    /**
     * Retrieve category tree data
     *
     * @return array
     */
    protected function _retrieve()
    {
        $categoryTree = $this->_getCategoryTree();

        Mage::dispatchEvent(
            'styla_connect_api_retrieve_category_tree',
            array(
                'category_tree' => $categoryTree,
            )
        );


        $categoryTree = $this->_getCollectionData($categoryTree);

        //perform filtering on every level of the result tree
        $categoryTree = $this->_filterCategoryData($categoryTree);

        return $categoryTree;
    }

    /**
     * Filter-out any restricted data, by working through every level of the tree
     *
     * @param array $category
     * @return array
     */
    protected function _filterCategoryData($category)
    {
        $filteredData = $this->getFilter()->out($category);
        if (isset($filteredData['children']) && !empty($filteredData['children'])) {
            $childrenData = array();
            foreach ($filteredData['children'] as $childData) {
                $childData = $this->_filterCategoryData($childData);

                $childrenData[] = $childData;
            }

            $filteredData['children'] = $childrenData;
        } elseif (isset($filteredData['children'])) {
            unset($filteredData['children']); //remove empty child nodes
        }

        return $filteredData;
    }

    public function dispatch()
    {
        switch ($this->getActionType() . $this->getOperation()) {
            /* Retrieve */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieve');
                $retrievedData = $this->_retrieve();

                //filtering is done on each tree level, as opposed to here:
                //$filteredData  = $this->getFilter()->out($retrievedData);
                $this->_render($retrievedData);
                break;

            default:
                parent::dispatch();
        }
    }

    /**
     *
     * @return Varien_Data_Tree_Node
     */
    protected function _getCategoryTree()
    {
        $categoryId = $this->getRequest()->getParam('id');

        if (!$categoryId) {
            if(!$storeId = $this->getRequest()->getParam('store')) {
                $storeId = Mage::app()->getDefaultStoreView()->getId();
            }
            $categoryId   = Mage::app()->getStore($storeId)->getRootCategoryId();
        }

        $categoryTree = $this->_getCategoryApi()->tree($categoryId);

        return $categoryTree;
    }

    /**
     *
     * @return Styla_Connect_Model_Api2_Category_Tree
     */
    protected function _getCategoryApi()
    {
        return Mage::getSingleton('styla_connect/api2_category_tree');
    }

    protected function _prepareCategoryTreeForResponse($categoryTree)
    {
        //run the styla api converters, to get the final fields we're looking for
        $this->_getResponseConfig()->prepareStylaApiResponse($categoryTree, "category");
    }

    /**
     *
     * @param Varien_Data_Tree_Node $node
     * @return array
     */
    protected function _getCollectionData($node)
    {
        $this->_prepareCategoryTreeForResponse($node);

        $result             = $node->getData();
        $result['children'] = array();

        foreach ($node->getChildren() as $child) {
            $result['children'][] = $this->_getCollectionData($child);
        }

        return $result;
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
