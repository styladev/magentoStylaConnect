<?php

class Styla_Connect_MagazineController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $path = $this->getRequest()->getParam('path');
        $api = $this->_getApi();
         
        if(!($pageData = $api->requestPageData($path))) {
            $this->_forward('noRoute');
            return;
        }
        
        Mage::register('current_magazine_data', $pageData);
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    protected function _getApi()
    {
        return Mage::getSingleton('styla_connect/styla_api');
    }
}