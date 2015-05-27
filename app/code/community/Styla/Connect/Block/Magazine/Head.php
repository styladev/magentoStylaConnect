<?php
class Styla_Connect_Block_Magazine_Head extends Styla_Connect_Block_Magazine
{
    protected function _toHtml()
    {
        $headHtml = "";
        
        $magazineData = $this->getMagazineData();
        if($magazineData) {
            $seoJson = $magazineData->getSeoData();
            if($seoJson->html && $seoJson->html->head) {
                $headHtml = (string)$seoJson->html->head;
            }
        }
        
        return $headHtml;
    }
}