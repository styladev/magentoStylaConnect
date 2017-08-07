<?php

/**
 * Class Styla_Connect_Block_Magazine_Content
 */
class Styla_Connect_Block_Magazine_Content extends Styla_Connect_Block_Magazine
{

    public function getNoscript()
    {
        return $this->getPage()
            ->getNoScript();
    }

    public function getRootPath()
    {
        $helper   = Mage::helper('styla_connect');
        $magazine = $helper->getCurrentMagazine();

        return $helper->getMagazineRootPath($magazine);
    }

    public function getPluginVersion()
    {
        return Mage::helper('styla_connect')->getPluginVersion();
    }
}
