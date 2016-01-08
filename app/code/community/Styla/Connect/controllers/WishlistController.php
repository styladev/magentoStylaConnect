<?php
require_once('Mage/Wishlist/controllers/IndexController.php');

/**
 * Class Styla_Connect_WishlistController
 */
class Styla_Connect_WishlistController extends Mage_Wishlist_IndexController
{
    public function preDispatch()
    {
        //default preDispatch of the wishlist controller would redirect us to login page. we don't need that.
        return Mage_Core_Controller_Front_Action::preDispatch();
    }

    public function addAction()
    {
        $helper = Mage::helper('wishlist');
        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
            //wishlist feature is not enabled -> 501 Not Implemented
            return $this->setResponse(
                501,
                array('error' => $helper->__('Could not find wishlist'))
            );
        }

        $product = $this->_initProduct();
        if (!$product) {
            //product does not exists -> 404 not found
            return $this->setResponse(
                404,
                array('error' => $helper->__('Cannot specify product.'))
            );
        }

        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            //wishlist needs the user to be logged in -> 401 Unauthorized
            return $this->setResponse(
                401,
                array('error' => $helper->__('You need be logged in to use the wishlist'))
            );
        }

        try {
            if (!$this->_addProductToWishList($product)) {
                return $this->setResponse(500, $helper->__('Cannot update wishlist'));
            }

            //all good at this point
            return $this->setResponse(
                200,
                array('success' => true)
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $this->setResponse(500, $helper->__('Cannot update wishlist'));
        }
    }

    /**
     * Add the item to wish list
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _addProductToWishList($product)
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return false;
        }

        $requestParams = $this->getRequest()->getParams();
        $buyRequest    = new Varien_Object($requestParams);

        $result = $wishlist->addNewItem($product, $buyRequest);
        if (is_string($result)) {
            Mage::throwException($result);
        }
        $wishlist->save();

        Mage::dispatchEvent(
            'wishlist_add_product',
            array(
                'wishlist' => $wishlist,
                'product'  => $product,
                'item'     => $result,
            )
        );

        Mage::helper('wishlist')->calculate();
    }

    /**
     * @param       $statusCode
     * @param array $body
     * @return $this
     */
    protected function setResponse($statusCode, $body = array())
    {
        $this->getResponse()
            ->setHeader('HTTP/1.0', $statusCode, true)
            ->setHeader('Content-type', 'application/json');

        if ($body) {
            $this->getResponse()->setBody(json_encode($body));
        }

        return $this;
    }

    /**
     *
     * @return boolean|Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $productId = $this->getRequest()->getParam('product');
        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')->load($productId);

        return $product && $product->getId() ? $product : false;
    }
}