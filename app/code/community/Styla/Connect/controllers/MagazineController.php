<?php

/**
 * Class Styla_Connect_MagazineController
 */
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

        $layout = $this->getLayout();
        $layoutHelper = Mage::helper('styla_connect/layout');
        $layoutHelper->setStylaData($page, $layout);

        $this->renderLayout();
    }
}
