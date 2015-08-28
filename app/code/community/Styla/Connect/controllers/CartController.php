<?php
require_once "Mage/Checkout/controllers/CartController.php";

class Styla_Connect_CartController extends Mage_Checkout_CartController
{
    const EVENT_GET_CART_UPDATE_HTML = "styla_connect_cart_update_html";
    const EVENT_GET_CART_METADATA = "styla_connect_cart_metadata";
    
    /**
     * Add product to shopping cart action, from a styla script request
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();

            /**
             * Check product availability
             */
            if (!$product) {
                throw new Exception('Error initializing product.');
            }

            $cart->addProduct($product, $params)
                ->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array(
                    'product' => $product,
                    'request' => $this->getRequest(),
                    'response' => $this->getResponse()
                )
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if ($cart->getQuote()->getHasError()) {
                    throw new Exception("Quote error.");
                }
            }
        } catch (Mage_Core_Exception $e) {
            /**
             * TODO: we should be returning the error messages back to user
             */
            /* $messages = array_unique(explode("\n", $e->getMessage()));
            foreach ($messages as $message) {
                $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
            } */
            Mage::logException($e);
            $this->getResponse()->setHeader('HTTP/1.0','404',true);
            return;
        } catch (Exception $e) {
            Mage::logException($e);
            
            $this->getResponse()->setHeader('HTTP/1.0','404',true);
            return;
        }
        
        $resultArray = array(
            'html' => $this->_getCartHtmlContent(),
            'meta' => $this->_getCartMetaData(),
        );
        
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($resultArray));
        
        return;
    }
    
    /**
     * Return all blocks defined in the cart_update_content layout block.
     * This will return an array, contaning html of all children of this block.
     * 
     * @return array
     */
    protected function _getCartHtmlContent()
    {
        $this->loadLayout();
        $layout = $this->getLayout();

        /** @var Styla_Connect_Block_Cart_Contentlist $block */
        $block = $layout->getBlock('styla.cart_update_content');
        $blockHtmlArray = $block->toHtmlArray();
        
        $transportObject = new Varien_Object();
        $transportObject->setBlockHtmlArray($blockHtmlArray);
        Mage::dispatchEvent(self::EVENT_GET_CART_UPDATE_HTML, array('transport_object' => $transportObject, 'block' => $block));
        
        $blockHtmlArray = $transportObject->getBlockHtmlArray();
        
        return $blockHtmlArray;
    }
    
    /**
     * Get the basic information about the current cart
     * 
     * @return array
     */
    protected function _getCartMetaData()
    {
        $cart = $this->_getCart();
        $quote = $cart->getQuote();
        
        $cartMetaData = array(
            'grand_total'   => $quote->getGrandTotal(),
            'subtotal'   => $quote->getSubtotal(),
            'subtotal_with_discount' => $quote->getSubtotalWithDiscount(),
            'num_items' => count($cart->getItems()),
            'items_qty' => $quote->getItemsQty(),
        );
        
        $transportObject = new Varien_Object();
        $transportObject->setCartMetaData($cartMetaData);
        Mage::dispatchEvent(self::EVENT_GET_CART_METADATA, array('transportObject' => $transportObject, 'cart' => $cart));
        
        $cartMetaData = $transportObject->getCartMetaData();
        
        return $cartMetaData;
    }
}