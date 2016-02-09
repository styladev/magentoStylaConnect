<?php
class Styla_Connect_Model_Adminhtml_Config_Data extends Mage_Adminhtml_Model_Config_Data
{
    /**
     * Convert the inserted website and store ids into scope, scope_id
     * 
     */
    public function resolveScope()
    {
        $this->_getScope();
    }
}