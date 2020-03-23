<?php

/**
 * Class Styla_Connect_Model_Magazine
 *
 * @method getStore
 * @method getFrontName
 * @method getClientName
 * @method getNavigationLabel
 * @method $this setStore($storeId)
 * @method $this setFrontName($frontName)
 * @method $this setIsDefault($isDefault)
 * @method $this setClientName($clientName)
 */
class Styla_Connect_Model_Magazine
    extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'styla_magazine';

    protected $_eventObject = 'magazine';

    public function loadDefault()
    {
        $this->load(1, 'is_default');

        return $this;
    }

    public function loadByFrontName($frontName)
    {
        if(!$frontName) {
            $collection = $this->getCollection();
            $item = $collection->addFieldToFilter('front_name', array('null' => true))
                    ->getFirstItem();
            
            if($item->getId()) {
                return $this->load($item->getId());
            } else {
                return $item;
            }
        }
        
        $this->load($frontName, 'front_name');

        return $this;
    }


    protected function _construct()
    {
        $this->_init('styla_connect/magazine');
    }

    public function isActive()
    {
        return (int)$this->getData('is_active') === 1;
    }

    public function isDefault()
    {
        return (int)$this->getData('is_default') === 1;
    }

    public function useMagentoLayout()
    {
        return (int)$this->getData('use_magento_layout') === 1;
    }

    public function includeInNavigation()
    {
        return (int)$this->getData('include_in_navigation') === 1;
    }
}
