<?php
/**
 * Created by IntelliJ IDEA.
 * User: justus
 * Date: 22.05.15
 * Time: 18:57
 */ 
class Styla_Connect_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_magazineData;
    
    /**
     * 
     * @return Varien_Object|null
     */
    public function getMagazineData()
    {
        if(!$this->_magazineData) {
            $this->_magazineData = Mage::registry('current_magazine_data');
        }
        
        return $this->_magazineData;
    }
    
    /**
     * Get the SEO tags from the Styla API magazine response
     * 
     * @return array
     */
    public function getMagazineSeoTagNames()
    {
        $magazineData = $this->getMagazineData();
        if(!$magazineData) {
            return array();
        }
        
        $seoData = $magazineData->getSeoData();
        if(!$seoData) {
            return array();
        }
        
        $seoTags = array();
        if(isset($seoData->tags)) {
            foreach($seoData->tags as $tag) {
                $tagId = $tag->tag;
                
                if(isset($tag->attributes->name)) {
                    $tagId .= "-" . $tag->attributes->name;
                }
                
                $seoTags[$tagId] = $tag;
            }
        }
        
        return $seoTags;
    }
}