<?php
class Styla_Connect_Block_Magazine_Content extends Styla_Connect_Block_Magazine
{
    const SCRIPT_TAG = '<div id="stylaMagazine"></div><script defer="defer" id="amazineEmbed" src="%s" data-language="%s" data-rootpath="%s"></script>';
    
    protected function _toHtml()
    {
        $html = "";
        
        if($scriptHtml = $this->_getScript()) {
            $html .= $scriptHtml;
        }
        
        if($noscriptHtml = $this->_getNoscript()) {
            $html .= $noscriptHtml;
        }
        
        return $html;
    }
    
    protected function _getNoscript()
    {
        $html = "";
        
        $magazineData = $this->getMagazineData();
        if($magazineData) {
            $seoJson = $magazineData->getSeoData();
            if(isset($seoJson->html) && $seoJson->html->body) {
                $html = (string)$seoJson->html->body;
            }
        }
        
        return $html;
    }
    
    protected function _getScript()
    {
        $html = "";
        
        $magazineData = $this->getMagazineData();
        if($magazineData) {
            $jsUrl = $magazineData->getScriptUrl();
            if($jsUrl) {
                $language = $this->getConfigHelper()->getLanguageCode();
                $routeName = trim($this->getConfigHelper()->getRouteName(), "/");
                
                $html = sprintf(self::SCRIPT_TAG, $jsUrl, $language, $routeName);
            }
        }
        
        return $html;
    }
}