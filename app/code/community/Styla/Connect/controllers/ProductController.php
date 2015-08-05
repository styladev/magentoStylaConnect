<?php
class Styla_Connect_ProductController extends Mage_Core_Controller_Front_Action
{
    /**
     * Load the product's details and return them as a json
     * This is used in ajax calls from the magazine.
     * 
     */
    public function infoAction()
    {
        $product = $this->_initProduct();
        if(!$product) {
            $this->getResponse()->setHeader('HTTP/1.0','404',true);
            return;
        }
        
        try {
            $productInfo = $this->_getProductInfo();
            $productInfo->setProduct($product);

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($productInfo->getInfo()));
            
        } catch(Exception $e) {
            Mage::logException($e);
            
            $this->getResponse()->setHeader('HTTP/1.0','404',true);
            return;
        }
    }
    
    /**
     * 
     * @return boolean|Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $productSku = $this->getRequest()->getParam('sku');
        if(!$productSku) {
            return false;
        }
        
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
        return $product && $product->getId() ? $product : false;
    }
    
    /**
     * 
     * @return Styla_Connect_Model_Product_Info
     */
    protected function _getProductInfo()
    {
        return Mage::getSingleton('styla_connect/product_info');
    }
}