<?php
class Styla_Connect_Adminhtml_Styla_ApiController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()->renderLayout();
    }
    
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        try {
            if(empty($post)) {
                Mage::throwException("Invalid form data.");
            }
            
            $stylaData = $post['styla'];
            $this->_validateData($stylaData);
            
            $this->_getOauthConnector()->grantStylaApiAccess($stylaData, true);
            
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Magento API OAuth login data sent successfully to Styla."));
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect("adminhtml/system_config/edit/section/styla_connect");
    }
    
    protected function _validateData($data)
    {
        if(!$data['email'] || !$data['password']) {
            throw new Exception($this->__("Invalid form data."));
        }
    }
    
    protected function _getOauthConnector()
    {
        return Mage::getSingleton('styla_connect/styla_api_oauth_connector');
    }
}
