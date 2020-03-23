<?php

/**
 * Class Styla_Connect_Helper_Layout
 *
 */
class Styla_Connect_Helper_Layout extends Styla_Connect_Helper_Data
{
    /**
     * one entry for all styla layout functions
     * 
     * @param type $page
     * @param type $layout
     */
    public function setStylaData($page, $layout)
    {
        $this->setLayoutOption($layout);
        $this->setBaseMetaData($page, $layout);
    }

    /**
     * set root template accordingly to the config,
     * with magento layout or without
     */
    public function setLayoutOption($layout)
    {
        /** @var Mage_Core_Block_Template $root */
        $root = $layout->getBlock('root');

        /** @var Mage_Core_Block_Template $head */
        $head = $layout->getBlock('head');

        if ($this->useMagentoLayout()) {
            $root->setTemplate('page/1column.phtml');
        } else {
            $root->setTemplate('styla/connect/magazine.phtml');
            $head->setTemplate('styla/connect/head.phtml');
        }

    }

    /**
     * @return bool
     */
    public function useMagentoLayout()
    {
        return $this->getCurrentMagazine()
            ->useMagentoLayout();
    }

    /**
     * Overwrite base meta data if set
     *
     * @param Styla_Connect_Model_Page $page
     */
    public function setBaseMetaData(Styla_Connect_Model_Page $page, $layout)
    {
        $head = $layout->getBlock('head');
        if ($head) {
            $head->addData($page->getBaseMetaData());
        }
    }
}
