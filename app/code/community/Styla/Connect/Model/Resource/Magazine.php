<?php

/**
 * Class Styla_Connect_Model_Resource_Magazine
 */
class Styla_Connect_Model_Resource_Magazine extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('styla_connect/magazine', 'id');
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $fieldName = $field;
        $field     = $this->_getReadAdapter()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($field . '=?', $value)
            ->limit(1);

        if ($fieldName === 'front_name') {
            //select the magazine with the right front name and store
            $select
                ->where('store_id = ? OR is_default = 1', Mage::app()->getStore()->getId())
                ->order('is_default ASC');
        }

        return $select;
    }
}
