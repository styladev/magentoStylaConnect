<?php
require_once('Mage/Wishlist/controllers/IndexController.php');

class Styla_Connect_WishlistController extends Mage_Wishlist_IndexController
{
    public function preDispatch()
    {
        //default preDispatch of the wishlist controller would redirect us to login page. we don't need that.
        return Mage_Core_Controller_Front_Action::preDispatch();
    }
    
    public function addAction()
    {
        //check if the customer is logged in, and the product requested is real
        $product = $this->_initProduct();
        if(!$product || !Mage::getSingleton('customer/session')->isLoggedIn() || !Mage::getStoreConfigFlag('wishlist/general/active')) {
            $this->_responseError();
            return;
        }
        
        if(!$this->_addProductToWishList($product)) {
            $this->_responseError();
            return;
        }
        
        //all good at this point, we may think about a return value
        
        $resultArray = array(
            'success'   => true
        );
        
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($resultArray));
        
        return;
    }
    
    /**
     * Add the item to wish list
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _addProductToWishList($product)
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return false;
        }

        $session = Mage::getSingleton('customer/session');
        $success = false;

        try {
            $requestParams = $this->getRequest()->getParams();
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            Mage::helper('wishlist')->calculate();

            $success = true;
        } catch (Mage_Core_Exception $e) {
            return false;
        }
        catch (Exception $e) {
            return false;
        }

        return $success;
    }
    
    protected function _responseError()
    {
        $this->getResponse()->setHeader('HTTP/1.0','404',true);
    }
    
    /**
     * 
     * @return boolean|Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $productId = $this->getRequest()->getParam('product');
        if(!$productId) {
            return false;
        }
        
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product && $product->getId() ? $product : false;
    }
}