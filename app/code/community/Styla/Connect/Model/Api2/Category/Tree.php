<?php

/**
 * Class Styla_Connect_Model_Api2_Category_Tree
 *
 */
class Styla_Connect_Model_Api2_Category_Tree extends Mage_Catalog_Model_Category_Api
{
    /**
     * Retrieve category tree
     *
     * @param int        $parentId
     * @param string|int $store
     * @return array
     */
    public function tree($parentId = null, $store = null)
    {
        if (is_null($parentId) && !is_null($store)) {
            $parentId = Mage::app()->getStore($this->_getStoreId($store))->getRootCategoryId();
        } elseif (is_null($parentId)) {
            $parentId = 1;
        }
        
        //we may wanna limit the number of levels of categories loaded at once into the tree
        $maxCategoryLevels = Mage::getStoreConfig('styla_connect/admin/max_category_children');
        
        /* @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
        $tree = Mage::getResourceSingleton('styla_connect/catalog_category_tree');
        $tree->loadMaxChildrenLevels($parentId, array('image', 'name', 'is_active'), $maxCategoryLevels);
        
        $root = $tree->getNodeById($parentId);
        
        if ($root && $root->getId() == 1) {
            $root->setName(Mage::helper('catalog')->__('Root'));
        }
        
        return $root;
    }
}