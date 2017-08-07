<?php

/**
 * Class Styla_Connect_Helper_Data
 *
 */
class Styla_Connect_Helper_Data extends Mage_Core_Helper_Abstract
{
    /** @var Styla_Connect_Model_Magazine */
    protected $_currentMagazine;

    /** @var Styla_Connect_Model_Page */
    protected $_currentPage;

    protected $_rootPaths = array();

    protected $_isDeveloperMode;

    protected $_apiVersion;

    const URL_SEO_PROD    = 'http://seo.styla.com/';
    const URL_ASSETS_PROD = '//cdn.styla.com/';

    const URL_PART_JS  = 'scripts/clients/%s.js?v=%s';
    const URL_PART_CSS = 'styles/clients/%s.css?v=%s';

    const URL_VERSION_PROD = 'http://live.styla.com/';
    const URL_PART_VERSION = 'api/version/%s';

    const ASSET_TYPE_JS  = 'js';
    const ASSET_TYPE_CSS = 'css';

    /**
     * @return Styla_Connect_Model_Page|null
     */
    public function getCurrentPage()
    {
        if (!$this->_currentPage) {
            $this->_currentPage = Mage::registry('current_magazine_page');
        }

        return $this->_currentPage;
    }

    /**
     * @return Styla_Connect_Model_Magazine|null
     */
    public function getCurrentMagazine()
    {
        if (!$this->_currentMagazine) {
            $this->_currentMagazine = Mage::registry('current_magazine');
        }

        return $this->_currentMagazine;
    }

    /**
     * @return string
     */
    public function getPluginVersion()
    {
        return (string)Mage::getConfig()->getModuleConfig('Styla_Connect')->version;
    }

    /**
     * Is the module in developer mode?
     *
     * @return bool
     */
    public function isDeveloperMode()
    {
        if (null === $this->_isDeveloperMode) {
            $this->_isDeveloperMode = Mage::getStoreConfigFlag('styla_connect/developer/is_developer_mode');
        }
        return $this->_isDeveloperMode;
    }

    /**
     * Get the overridden url, if the module is in developer mode.
     * Returns FALSE if the url is not overridden, or the developer mode is disabled.
     *
     * @param string $url
     * @return boolean|string
     */
    public function getDeveloperModeUrl($url)
    {
        if (!$this->isDeveloperMode()) {
            return false;
        }

        $path = sprintf('styla_connect/developer/override_%s_url', $url);
        $url  = Mage::getStoreConfig($path);
        if ($url) {
            $url = rtrim($url, "/") . "/";
        }

        return $url;
    }

    public function getAbsoluteMagazineUrl(Styla_Connect_Model_Magazine $magazine)
    {
        return Mage::getBaseUrl() . $magazine->getFrontName();
    }

    public function getMagazineRootPath(Styla_Connect_Model_Magazine $magazine)
    {
        if (!isset($this->_rootPaths[$magazine->getId()])) {
            $frontName = $magazine->getFrontName();
            //get the url to the magazine page, strip index.php from it. this gives me the root path for a magazine
            $url = parse_url(str_replace('/index.php/', '/', Mage::getUrl($frontName)));

            $this->_rootPaths[$magazine->getId()] = isset($url['path']) ? $url['path'] : '';
        }

        return $this->_rootPaths[$magazine->getId()];
    }

    /**
     * Get the SEO Api Url
     *
     * @return string
     */
    public function getApiSeoUrl()
    {
        if ($overrideUrl = $this->getDeveloperModeUrl('seo')) {
            $url = $overrideUrl;
        } else {
            $url = self::URL_SEO_PROD;
        }

        return $url;
    }


    /**
     * Get the Assets Url (script,css)
     *
     * @param string $type
     * @return string
     */
    public function getAssetsUrl($type)
    {
        //is the url overridden in developer mode of the styla module?
        if ($overrideUrl = $this->getDeveloperModeUrl('cdn')) {
            $url = $overrideUrl;
        } else {
            $url = self::URL_ASSETS_PROD;
        }

        $clientName = $this->getClientName();
        $apiVersion = $this->getCurrentApiVersion();

        $assetsUrl = false;
        switch ($type) {
            case self::ASSET_TYPE_JS:
                $assetsUrl = $url . sprintf(self::URL_PART_JS, $clientName, $apiVersion);
                break;
            case self::ASSET_TYPE_CSS:
                $assetsUrl = $url . sprintf(self::URL_PART_CSS, $clientName, $apiVersion);
                break;
        }

        return $assetsUrl;
    }

    /**
     * Get the Content Version Number API Url
     *
     * @return string
     */
    public function getApiVersionUrl()
    {
        if ($overrideUrl = $this->getDeveloperModeUrl('api')) {
            $url = $overrideUrl;
        } else {
            $url = self::URL_VERSION_PROD;
        }

        $clientName = $this->getClientName();
        $versionUrl = sprintf($url . self::URL_PART_VERSION, $clientName);

        return $versionUrl;
    }

    public function getClientName()
    {
        return $this
            ->getCurrentMagazine()
            ->getClientName();
    }

    /**
     * Get the current version number of the content (script, css)
     *
     * @return string
     */
    public function getCurrentApiVersion()
    {
        if (null === $this->_apiVersion) {
            $this->_apiVersion = $this->_getApi()->getCurrentApiVersion();
        }

        return $this->_apiVersion;
    }


    /**
     * Get the content language code
     *
     * @return string
     */
    public function getLanguageCode()
    {
        return Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId());
    }

    public function getCacheLifetime()
    {
        return Mage::getStoreConfig('styla_connect/basic/cache_lifetime');
    }

    public function isUsingRelativeProductUrls()
    {
        return Mage::getStoreConfigFlag('styla_connect/basic/use_relative_product_url');
    }

    /**
     * @return Styla_Connect_Model_Styla_Api
     */
    protected function _getApi()
    {
        return Mage::getSingleton('styla_connect/styla_api');
    }

}
