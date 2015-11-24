<?php
class Styla_Connect_Block_Magazine_Head extends Styla_Connect_Block_Magazine
{
    public function getMetaTags()
    {
        return $this->getPage()
            ->getAdditionalMetaTags();
    }
}