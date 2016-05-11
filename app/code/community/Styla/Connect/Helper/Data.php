<?php

/**
 * Class Styla_Connect_Helper_Data
 *
 * @author ecocode GmbH <jk@ecocode.de>
 * @author Justus Krapp <jk@ecocode.de>
 */
class Styla_Connect_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_currentPage;

    /**
     * @return Varien_Object|null
     */
    public function getCurrentPage()
    {
        if (!$this->_currentPage) {
            $this->_currentPage = Mage::registry('current_magazine_page');
        }

        return $this->_currentPage;
    }
}