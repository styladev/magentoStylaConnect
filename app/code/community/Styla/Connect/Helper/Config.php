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
     * @return array
     */
    public function getApiConfigurationFields()
    {
        return $this->_apiConfigurationFields;
    }
    
    /**
     * Get the proper field configuration path, according to module's operating mode (stage,prod)
     * 
     * @param string $fieldName
     * @param string $mode
     * @param bool $usingNameAsPath
     * @return boolean|string
     */
    public function getApiConfigurationFieldByMode($fieldName, $mode, $usingNameAsPath = false)
    {
        $path = $usingNameAsPath ? $fieldName : (isset($this->_apiConfigurationFields[$fieldName]) ? $this->_apiConfigurationFields[$fieldName] : false);
        if(!$path) {
            return false;
        }
        
        return $path . "_" . $mode;
    }

    /**
     *
     * @return Mage_Core_Model_Config
     */
    public function getConfiguration()
    {
        if (!$this->_configuration) {
            $this->_configuration = Mage::getConfig();
        }

        return $this->_configuration;
    }

    /**
     * Get the Client's username
     *
     * @return string
     */
    public function getUsername($mode = null, $store = null)
    {
        $mode = $this->getMode($mode, $store);
        $path = $this->getApiConfigurationFieldByMode('styla_connect/basic/username', $mode, true);
        
        $username = Mage::getStoreConfig($path, $store);
        return $username;
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
     * Is the frontend navigation menu button enabled?
     * 
     * @return bool
     */
    public function isNavigationLinkEnabled()
    {
        return (bool)Mage::getStoreConfig("styla_connect/frontend/menu_link_enabled");
    }
    
    /**
     * 
     * @return string
     */
    public function getNavigationLinkLabel()
    {
        return Mage::getStoreConfig("styla_connect/frontend/menu_link_label");
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

    /**
     * 
     * @param string|null $mode
     * @param string|null $store
     * @return string
     */
    public function getApiSeoUrl($mode = null, $store = null)
    {
        $mode = $this->getMode($mode);
        $path = $this->getApiConfigurationFieldByMode('styla_connect/basic/seo_url', $mode, true);
        
        $seoUrl = Mage::getStoreConfig($path, $store);
        return $seoUrl;
    }

    public function getApiJsUrl($mode = null, $store = null)
    {
        $mode = $this->getMode($mode);
        $path = $this->getApiConfigurationFieldByMode('styla_connect/basic/js_url', $mode, true);
        
        $jsUrl = Mage::getStoreConfig($path, $store);
        return $jsUrl;
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
    public function getMode($mode = null, $store = null)
    {
        if($mode) {
            return $mode;
        }
        
        $configuredMode = Mage::getStoreConfig('styla_connect/basic/mode', $store);

        return $configuredMode ? $configuredMode : self::MODE_PRODUCTION;
    }
    
    /**
     * 
     * @deprecated
     * @param mixed $scopeData
     * @return array
     */
    public function getScope($scopeData = null)
    {
        if($scopeData) {
            return $scopeData;
        }
        
        return array('scope' => 'default', 'scope_id' => null);
    }

    /**
     * Is the module already registered with Styla in the operating mode in question (stage, prod)
     *
     * @return bool
     */
    public function isConfiguredForThisMode($mode = null)
    {
        $website = Mage::app()->getRequest()->getParam('website');
        $store   = Mage::app()->getRequest()->getParam('store');
        $scope = $this->resolveScope($website, $store);

        if(!$mode) {
            $mode = $this->getAdminMode($scope);
        }

        $clientPath = $this->getApiConfigurationFieldByMode('client', $mode);
        $client = $this->getConfigurationNode($clientPath, $scope->getScope(), $scope->getScopeId());

        return $client ? true : false;
    }
    
    /**
     * Get the module's operating mode, as in the current scope selected in admin configuration
     * 
     * @param mixed $scope
     * @return string
     */
    public function getAdminMode($scope = null)
    {
        if(!$scope) {
            $website = Mage::app()->getRequest()->getParam('website');
            $store   = Mage::app()->getRequest()->getParam('store');
            $scope = $this->resolveScope($website, $store);
        }
        
        return $this->getConfigurationNode('styla_connect/basic/mode', $scope->getScope(), $scope->getScopeId());
    }

    /**
     * Save the connection configuration for the module. This data is taken from a response to the Styla API.
     * See the Api Connector for more details.
     *
     * @param array $connectionData
     * @param string $mode
     * @param array $scopeData array('scope' => X, 'scope_id' => Y)
     * @throws Styla_Connect_Exception
     */
    public function updateConnectionConfiguration(array $connectionData, $mode, $scopeData)
    {
        $configuration = $this->getConfiguration();

        foreach ($this->_apiConfigurationFields as $fieldName => $configurationPath) {
            if (!isset($connectionData[$fieldName])) {
                throw new Styla_Connect_Exception("The configuration is missing required data: ".$fieldName);
            }

            $configurationPathByMode = $this->getApiConfigurationFieldByMode($fieldName, $mode);
            $configuration->saveConfig($configurationPathByMode, $connectionData[$fieldName], $scopeData['scope'], $scopeData['scope_id']);
        }

        /**
         * set the operating mode to the one we just configured,
         * as it easies the user into reconfiguring his store, a little bit
         */
        $configuration->saveConfig('styla_connect/basic/mode', $mode, $scopeData['scope'], $scopeData['scope_id']);

        //refresh the config cache
        $configuration->cleanCache();
    }
    
    /**
     * Get the website and store identifier.
     * Convert it to scope and scope_id identifiers.
     * 
     * Returns array('scope' => SCOPE, 'scope_id' => SCOPE_ID)
     * 
     * @param mixed $website
     * @param mixed $store
     * @return array
     */
    public function resolveScope($website, $store)
    {
        $configModel = Mage::getSingleton('styla_connect/adminhtml_config_data');
        $configModel->setWebsite($website);
        $configModel->setStore($store);
        $configModel->resolveScope();
        
        return $configModel;
    }

    /**
     * Get cached connection configuration for module operation mode $mode
     *
     * @deprecated after 0.1.1.4 as we now can store multiple configurations in the db
     * @param string $mode
     * @return bool|stdClass
     */
    public function getConnectionDataForMode($mode, $scopeData)
    {
        $connectionData = new stdClass();

        $hasConfigurationData = false;
        foreach ($this->_apiConfigurationFields as $fieldName => $configurationPath) {
            /**
             * try loading the cached connection data for this $mode
             *
             */
            $configurationPathByMode = $this->getApiConfigurationFieldByMode($configurationPath, $mode, true);

            $savedConfigurationValue = $this->getConfigurationNode($configurationPathByMode, $scopeData['scope'], $scopeData['scope_id']);
            if ($savedConfigurationValue) {
                $hasConfigurationData = true;

                $connectionData[$fieldName] = $savedConfigurationValue;
            }
        }

        return $hasConfigurationData ? $connectionData : false;
    }
    
    /**
     * Get a raw configuration value from the Magento Config, for the specifically selected scope and scope_id
     * 
     * @param string $path
     * @param mixed $scope
     * @param mixed $scopeId
     * @return null|string
     */
    public function getConfigurationNode($path = null, $scope = '', $scopeId = null)
    {
        $configuration = $this->getConfiguration();
        $value = $configuration->getNode($path, $scope, $scopeId);
        
        return $value instanceof Mage_Core_Model_Config_Element ? $value->asArray() : null;
    }

    /**
     * Returns false if no configuration found for current mode
     *
     * @return bool|array
     */
    public function getCachedConnectionData($mode = null, $scopeData = null)
    {
        $mode = $this->getMode($mode);
        $scopeData = $this->getScope($scopeData);

        $cachedConnectionData = $this->getConnectionDataForMode($mode, $scopeData);

        return $cachedConnectionData;
    }

    /**
     * Store the response from the Styla API Connector for the $moduleMode operation mode, so we don't have to call
     * styla again to get the same data
     * @param array    $connectionData
     * @param          $moduleMode
     * @throws Styla_Connect_Exception
     */
    public function cacheConnectionData(array $connectionData, $moduleMode, $scopeData)
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
            $configurationPathByMode = $this->getApiConfigurationFieldByMode($fieldName, $moduleMode);
            $configuration->saveConfig($configurationPathByMode, $connectionData[$fieldName], $scopeData['scope'], $scopeData['scope_id']);
        }

        //refresh the config cache
        $configuration->cleanCache();
    }
}