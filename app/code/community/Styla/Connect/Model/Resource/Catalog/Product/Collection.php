<?php

/**
 * Class Styla_Connect_Model_Resource_Catalog_Product_Collection
 */
class Styla_Connect_Model_Resource_Catalog_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{
    const CATEGORY_FILTER = 'styla_category_filter';

    /**
     * Specify category filter for product collection
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addCategoryFilter(Mage_Catalog_Model_Category $category)
    {
        $this->_productLimitationFilters[self::CATEGORY_FILTER] = $category;

        if (Mage::registry('_singleton/api2/server')) {
            $this->_applyZeroStoreProductLimitations();
        } else {
            Mage::throwException("This resource model should only be used in the API context.");
        }

        return $this;
    }

    /**
     * will only return products that are in at least one website
     *
     */
    public function addInWebsiteFilter()
    {
        $select = $this->getSelect();
        $select->join(
            array('inwebs' => $this->getTable('catalog/product_website')),
            'inwebs.product_id = e.entity_id',
            array()
        );

        return $this;
    }

    /**
     * @deprecated
     * @param string $searchTerm
     */
    public function addFulltextSearchTerm($searchTerm)
    {
        $select = $this->getSelect();

        $preparedTerms = Mage::getResourceHelper('catalogsearch')
            ->prepareTerms($searchTerm);

        $select->join(array('fs' => 'catalogsearch_fulltext'), 'fs.product_id = e.entity_id', array());
        $select->where(new Zend_Db_Expr('MATCH (fs.data_index) AGAINST (:query IN BOOLEAN MODE)'));
        $this->addBindParam(':query', implode(' ', $preparedTerms[0]));

        $select->group('e.entity_id');
    }

    /**
     * Apply limitation filters to collection base on API
     * Method allows using one time category product table
     * for combinations of category_id filter states
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _applyZeroStoreProductLimitations()
    {
        $filters = $this->_productLimitationFilters;

        $category    = $filters[self::CATEGORY_FILTER];
        $categoryIds = $this->_getValidIdsForCategory($category);

        $conditions = array(
            'cat_pro.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_pro.category_id IN(?)', $categoryIds),
        );
        $joinCond   = join(' AND ', $conditions);

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_pro'])) {
            $fromPart['cat_pro']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        } else {
            $this->getSelect()->join(
                array('cat_pro' => $this->getTable('catalog/category_product')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }
        $this->_joinFields['position'] = array(
            'table' => 'cat_pro',
            'field' => 'position',
        );

        return $this;
    }

    /**
     * For a given category, get all ids to other categories related to it.
     *
     * For a normal category, add ids of all it's parents
     * For an anchor - also add all it's children
     *
     * @param Mage_Catalog_Model_Category $category
     * @return array
     */
    protected function _getValidIdsForCategory(Mage_Catalog_Model_Category $category)
    {
        return explode(',', $category->getAllChildren());
    }
}
