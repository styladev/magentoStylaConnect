<?php
class Styla_Connect_Block_Magazine_Head extends Styla_Connect_Block_Magazine
{
    public function getHeadData()
    {
        $magazineData = $this->getMagazineData();
        if($magazineData) {
            $seoJson = $magazineData->getSeoData();
            if(isset($seoJson->html) && $seoJson->html->head) {
                return (string)$seoJson->html->head;
            }
        }
        
        return "";
    }
}