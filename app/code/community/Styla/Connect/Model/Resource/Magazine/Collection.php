<?php

/**
 * Class Styla_Connect_Model_Resource_Magazine_Collection
 */
class Styla_Connect_Model_Resource_Magazine_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('styla_connect/magazine');
    }

    public function joinStoreCode()
    {
        $this->getSelect()->joinLeft(
            array('s' => $this->getTable('core/store')),
            'main_table.store_id = s.store_id',
            array('store_code' => 's.code')
        );
    }

    public function addTopNavigationFilter($storeId = null)
    {
        $this->getSelect()
            ->where('main_table.store_id = ? OR is_default = 1', $storeId)
            ->where('include_in_navigation = 1')
            ->where('is_active = 1');

        return $this;
    }
}
