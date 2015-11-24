<?php

class Styla_Connect_MagazineController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $path = $this->getRequest()->getParam('path');

        if (empty($path)) {
            $path = '/';
        }
        $page = Mage::getModel('styla_connect/page')
            ->load($path);

        if (!$page->exist()) {
            $this->_forward('noRoute');

            return;
        }

        Mage::register('current_magazine_page', $page);

        $this->loadLayout();

        $this->setLayoutOption();
        $this->setBaseMetaData($page);

        $this->renderLayout();
    }

    /**
     * set root template accordingly to the config,
     * with magento layout or without
     */
    protected function setLayoutOption()
    {
        $layout = $this->getLayout();

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
    protected function useMagentoLayout()
    {
        return Mage::helper('styla_connect/config')
            ->isUsingMagentoLayout();
    }

    /**
     * Overwrite base meta data if set
     *
     * @param Styla_Connect_Model_Page $page
     */
    protected function setBaseMetaData(Styla_Connect_Model_Page $page)
    {
        $layout = $this->getLayout();

        $head = $layout->getBlock('head');
        if ($head) {
            $head->addData($page->getBaseMetaData());
        }
    }
}