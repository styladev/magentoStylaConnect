<?php
class Styla_Connect_Block_Cart_Contentlist extends Mage_Core_Block_Abstract
{
    /**
     * Load all child blocks and return their html content as individual array elements
     * 
     * @return array
     */
    public function toHtmlArray()
    {
        $html = array();
        
        foreach ($this->getSortedChildren() as $name) {
            $block = $this->getLayout()->getBlock($name);
            if (!$block) {
                Mage::throwException(Mage::helper('core')->__('Invalid block: %s', $name));
            }
            
            $html[$name] = $block->toHtml();
        }
        
        return $html;
    }
}