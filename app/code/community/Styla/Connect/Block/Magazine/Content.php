<?php

class Styla_Connect_Block_Magazine_Content extends Styla_Connect_Block_Magazine
{

    public function getNoscript()
    {
        return $this->getPage()
            ->getNoScript();
    }


    public function getRootPath()
    {
        return $this->getConfigHelper()->getRouteName();
    }
}