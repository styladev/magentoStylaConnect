<?php

/**
 * Class Styla_Connect_Helper_Config
 */
class Styla_Connect_Helper_Config
{
    const DEFAULT_ROUTE_NAME = 'magazin';

    const MODE_STAGE      = "stage";
    const MODE_PRODUCTION = "prod";

    protected $_apiConfigurationFields = array(
        'client' => 'styla_connect/basic/username',
        'seoUrl' => 'styla_connect/basic/seo_url',
        'jsUrl'  => 'styla_connect/basic/js_url',
    );

    protected $_configuration;

    /**
     *
     * @return Mage_Core_Model_Config
     */
    public function getConfiguration()
    {
        if (!$this->_configuration) {
            $this->_configuration = new Mage_Core_Model_Config();
        }

        return $this->_configuration;
    }

    /**
     * Get the global Client's username
     *
     * @return string
     */
    public function getUsername()
    {
        return Mage::getStoreConfig('styla_connect/basic/username');
    }

    /**
     * Get the route name for the router
     *
     * @return string
     */
    public function getRouteName()
    {
        $configuredRouteName = Mage::getStoreConfig('styla_connect/basic/frontend_name');
        $routeName           = $configuredRouteName ? $configuredRouteName : self::DEFAULT_ROUTE_NAME;

        return trim($routeName, "/")."/";
    }

    /**
     * @return mixed
     */
    public function getPluginVersion()
    {
        return (string) Mage::getConfig()->getModuleConfig("Styla_Connect")->version;
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

    public function isUsingMagentoLayout()
    {
        return Mage::getStoreConfigFlag('styla_connect/basic/use_magento_layout');
    }

    public function isUsingRelativeProductUrls()
    {
        return Mage::getStoreConfigFlag('styla_connect/basic/use_relative_product_url');
    }

    public function isModuleEnabled()
    {
        return Mage::getStoreConfigFlag('styla_connect/basic/enabled');
    }

    public function getApiSeoUrl()
    {
        return $this->parseUrl(Mage::getStoreConfig('styla_connect/basic/seo_url'));
    }

    public function getApiJsUrl()
    {
        return Mage::getStoreConfig('styla_connect/basic/js_url');
    }

    public function parseUrl($url)
    {
        return rtrim($url, "/")."/";
    }

    /**
     * Return the current operating mode STAGE/PROD of the module
     *
     * @return string
     */
    public function getMode()
    {
        $configuredMode = Mage::getStoreConfig('styla_connect/basic/mode');

        return $configuredMode ? $configuredMode : self::MODE_PRODUCTION;
    }

    /**
     * Is the module already registered with Styla in the operating mode in question (stage, prod)
     *
     * @return bool
     */
    public function isConfiguredForThisMode()
    {
        $currentMode   = $this->getMode();
        $configuredFor = Mage::getStoreConfig('styla_connect/basic/configured_for_mode');

        return $currentMode == $configuredFor;
    }

    /**
     * Save the connection configuration for the module. This data is taken from a response to the Styla API.
     * See the Api Connector for more details.
     *
     * @param array $connectionData
     * @throws Styla_Connect_Exception
     */
    public function updateConnectionConfiguration(array $connectionData)
    {
        $configuration = $this->getConfiguration();

        foreach ($this->_apiConfigurationFields as $fieldName => $configurationPath) {
            if (!isset($connectionData[$fieldName])) {
                throw new Styla_Connect_Exception("The configuration is missing required data: ".$fieldName);
            }

            $configuration->saveConfig($configurationPath, $connectionData[$fieldName]);
        }

        $configuration->saveConfig('styla_connect/basic/configured_for_mode', $this->getMode());

        //refresh the config cache
        $configuration->cleanCache();
    }

    /**
     * Get cached connection configuration for module operation mode $mode
     *
     * @param string $mode
     * @return bool|stdClass
     */
    public function getConnectionDataForMode($mode)
    {
        $connectionData = new stdClass();

        $hasConfigurationData = false;
        foreach ($this->_apiConfigurationFields as $fieldName => $configurationPath) {
            /**
             * try loading the cached connection data for this $mode
             *
             */
            $configurationPathByMode = $configurationPath."_".$mode;

            $savedConfigurationValue = Mage::getStoreConfig($configurationPathByMode);
            if ($savedConfigurationValue) {
                $hasConfigurationData = true;

                $connectionData[$fieldName] = $savedConfigurationValue;
            }
        }

        return $hasConfigurationData ? $connectionData : false;
    }

    /**
     * Returns false if no configuration found for current mode
     *
     * @return bool|array
     */
    public function getCachedConnectionData()
    {
        $mode = $this->getMode();

        $cachedConnectionData = $this->getConnectionDataForMode($mode);

        return $cachedConnectionData;
    }

    /**
     * Store the response from the Styla API Connector for the $moduleMode operation mode, so we don't have to call
     * styla again to get the same data
     * @param array    $connectionData
     * @param          $moduleMode
     * @throws Styla_Connect_Exception
     */
    public function cacheConnectionData(array $connectionData, $moduleMode)
    {
        $configuration = $this->getConfiguration();

        foreach ($this->_apiConfigurationFields as $fieldName => $configurationPath) {
            if (!isset($connectionData[$fieldName])) {
                throw new Styla_Connect_Exception(
                    "Invalid response from Styla API. Couldn't find required configuration value for ".$fieldName
                );
            }

            /**
             * save the cached value for this $moduleMode mode
             */
            $configurationPathByMode = $configurationPath."_".$moduleMode;
            $configuration->saveConfig($configurationPathByMode, $connectionData[$fieldName]);
        }

        //refresh the config cache
        $configuration->cleanCache();
    }
}