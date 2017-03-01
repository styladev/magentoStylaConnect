<?php

/**
 * Class Styla_Connect_Helper_Config
 */
class Styla_Connect_Helper_Config
{
    const DEFAULT_ROUTE_NAME = 'magazin';
    
    const URL_ASSETS_PROD       = 'http://cdn.styla.com/';
    const URL_PART_JS           = 'scripts/clients/%s.js?v=%s';
    const URL_PART_CSS          = 'styles/clients/%s.css?v=%s';
    
    const ASSET_TYPE_JS         = 'js';
    const ASSET_TYPE_CSS        = 'css';
    
    const URL_VERSION_PROD      = 'http://live.styla.com/';
    const URL_PART_VERSION      = 'api/version/%s';
    
    const URL_SEO_PROD          = 'http://seo.styla.com/';

    /** @deprecated since version 0.1.1.6 */
    const MODE_STAGE      = 'stage'; //@deprecated
    const MODE_PRODUCTION = 'prod'; //@deprecated

    /**
     * these fields may be returned after a successfull connection with Styla, and should be stored
     */
    protected $_apiConfigurationFields = array(
        'client' => 'styla_connect/basic/username',
        'rootpath' => 'styla_connect/basic/frontend_name'        
        //'seoUrl' => 'styla_connect/basic/seo_url', @deprecated
        //'jsUrl'  => 'styla_connect/basic/js_url', @deprecated
    );

    protected $_configuration;
    protected $_currentMode;
    protected $_isDeveloperMode;
    protected $_apiVersion;
    protected $_username;
    protected $_configuredRouteName;
    protected $_rootPath;

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
     * @deprecated since version 0.1.1.6 we don't have modes, anymore
     * @param string $fieldName
     * @param string $mode @deprecated
     * @param bool   $usingNameAsPath
     * @return boolean|string
     */
    public function getApiConfigurationFieldByMode($fieldName, $mode, $usingNameAsPath = false)
    {
        return $this->getApiConfigurationField($fieldName, $usingNameAsPath);
    }
    
    /**
     * Get the proper field configuration path, according to module's operating mode (stage,prod)
     *
     * @param string $fieldName
     * @param bool   $usingNameAsPath
     * @return boolean|string
     */
    public function getApiConfigurationField($fieldName, $usingNameAsPath = false)
    {
        $path = $usingNameAsPath ? $fieldName : (isset($this->_apiConfigurationFields[$fieldName]) ? $this->_apiConfigurationFields[$fieldName] : false);
        if (!$path) {
            return false;
        }

        return $path;
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
    public function getUsername($store = null)
    {
        if(isset($this->_username[$store])) {
            return $this->_username[$store];
        }
        
        $path = $this->getApiConfigurationField('styla_connect/basic/username', true);

        $username = Mage::getStoreConfig($path, $store);
        $this->_username[$store] = $username;
        
        return $username;
    }

    /**
     * Get the route name for the router.
     * Always appends a / character at the end.
     *
     * @return string
     */
    public function getRouteName()
    {
        $routeName = $this->getConfiguredRouteName();

        return trim($routeName, '/') . '/';
    }
    
    /**
     * Get the RootPath of the request.
     * It's always the name of the configured magazine frontname
     * 
     * @return string
     */
    public function getRootPath()
    {
        if(null === $this->_rootPath) {
            //get the url to the magazine page, strip index.php from it. this gives me the root path for a magazine
            $url = parse_url(str_replace('/index.php/', '/', Mage::getUrl($this->getRouteName())));
            
            $this->_rootPath = isset($url['path']) ? $url['path'] : '';
        }
        
        return $this->_rootPath;
    }
    
    /**
     * Get the route to the magazine, as configured by the user.
     * Returns the default value, if no configuration is found.
     * 
     * @return string
     */
    public function getConfiguredRouteName()
    {
        if(null === $this->_configuredRouteName) {
            $configuredRouteName = Mage::getStoreConfig('styla_connect/basic/frontend_name');
            $this->_configuredRouteName = $configuredRouteName ? $configuredRouteName : self::DEFAULT_ROUTE_NAME;
        }
        return $this->_configuredRouteName;
    }
    
    /**
     * Get the full public url of the styla magazine
     * 
     * @return string
     */
    public function getFullMagazineUrl()
    {
        $url = Mage::getBaseUrl() . $this->getConfiguredRouteName();
        
        return $url;
    }

    /**
     * Is the frontend navigation menu button enabled?
     *
     * @return bool
     */
    public function isNavigationLinkEnabled()
    {
        return (bool)Mage::getStoreConfig('styla_connect/frontend/menu_link_enabled');
    }

    /**
     *
     * @return string
     */
    public function getNavigationLinkLabel()
    {
        return Mage::getStoreConfig('styla_connect/frontend/menu_link_label');
    }

    /**
     * @return mixed
     */
    public function getPluginVersion()
    {
        return (string)Mage::getConfig()->getModuleConfig('Styla_Connect')->version;
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
     * Is the module in developer mode?
     * 
     * @return bool
     */
    public function isDeveloperMode()
    {
        if(null === $this->_isDeveloperMode) {
            $this->_isDeveloperMode = Mage::getStoreConfigFlag('styla_connect/developer/is_developer_mode');
        }
        return $this->_isDeveloperMode;
    }
    
    /**
     * Get the overriden url, if the module is in developer mode.
     * Returns FALSE if the url is not overriden, or the developer mode is disabled.
     * 
     * @param string $url
     * @return boolean|string
     */
    public function getDeveloperModeUrl($url)
    {
        if(!$this->isDeveloperMode()) {
            return false;
        }
        
        $path = sprintf('styla_connect/developer/override_%s_url', $url);
        $url = Mage::getStoreConfig($path);
        if($url) {
            $url = rtrim($url, "/") . "/";
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
        $url = false;
        
        //is the url overriden in developer mode of the styla module?
        if($overrideUrl = $this->getDeveloperModeUrl('cdn')) {
            $url = $overrideUrl;
        } else {
            $url = self::URL_ASSETS_PROD;
        }
        $clientName = $this->getUsername();
        $apiVersion = $this->getCurrentApiVersion();
        
        $assetsUrl = false;
        switch($type) {
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
        $url = false;
        
        if($overrideUrl = $this->getDeveloperModeUrl('api')) {
            $url = $overrideUrl;
        } else {
            $url = self::URL_VERSION_PROD;
        }
        
        $clientName = $this->getUsername();
        $versionUrl = sprintf($url . self::URL_PART_VERSION, $clientName);
        
        return $versionUrl;
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
     * @return Styla_Connect_Model_Styla_Api
     */
    protected function _getApi()
    {
        return Mage::getSingleton('styla_connect/styla_api');
    }

    /**
     * Get the SEO Api Url
     *
     * @return string
     */
    public function getApiSeoUrl()
    {
        $url = false;
        
        if($overrideUrl = $this->getDeveloperModeUrl('seo')) {
            $url = $overrideUrl;
        } else {
            $url = self::URL_SEO_PROD;
        }
        
        return $url;
    }

    /**
     * @deprecated There's a new method for it in the Page model, getScriptUrl()
     * The preloader is no longer used.
     *
     */
    public function getApiJsUrl($mode = null, $store = null)
    {
        $mode = $this->getMode($mode);
        $path = $this->getApiConfigurationFieldByMode('styla_connect/basic/js_url', $mode, true);

        $jsUrl = Mage::getStoreConfig($path, $store);
        return $jsUrl;
    }

    public function parseUrl($url)
    {
        return rtrim($url, '/') . '/';
    }

    /**
     * Return the current operating mode STAGE/PROD of the module
     *
     * @deprecated since version 0.1.1.6 use developer mode, instead
     * @return string
     */
    public function getMode($mode = null, $store = null)
    {
        return self::MODE_PRODUCTION;  //this is no longer relevant
    }

    /**
     * Get the current mode of operation, for the currently loaded store
     * The result is saved for later.
     * 
     * @deprecated since version 0.1.1.6
     *
     */
    public function getCurrentMode()
    {
        return self::MODE_PRODUCTION;  //this is no longer relevant
    }

    /**
     *
     * @deprecated
     * @param mixed $scopeData
     * @return array
     */
    public function getScope($scopeData = null)
    {
        if ($scopeData) {
            return $scopeData;
        }

        return array('scope' => 'default', 'scope_id' => null);
    }

    /**
     * Is the module already registered with Styla in the operating mode in question (prod or developer)
     *
     * @return bool
     */
    public function isConfiguredForThisMode()
    {
        $website = Mage::app()->getRequest()->getParam('website');
        $store   = Mage::app()->getRequest()->getParam('store');
        $scope   = $this->resolveScope($website, $store);

        //basically, checks if the client name was already filled as it's the only thing
        //that we need.
        $clientPath = $this->getApiConfigurationField('client');
        $client     = $this->getConfigurationNode($clientPath, $scope->getScope(), $scope->getScopeId());

        return $client ? true : false;
    }

    /**
     * Get the module's operating mode, as in the current scope selected in admin configuration
     *
     * @deprecated since version 0.1.1.6
     * @param mixed $scope
     * @return string
     */
    public function getAdminMode($scope = null)
    {
        if (!$scope) {
            $website = Mage::app()->getRequest()->getParam('website');
            $store   = Mage::app()->getRequest()->getParam('store');
            $scope   = $this->resolveScope($website, $store);
        }

        return $this->getConfigurationNode('styla_connect/basic/mode', $scope->getScope(), $scope->getScopeId());
    }

    /**
     * Save the connection configuration for the module. This data is taken from a response to the Styla API.
     * See the Api Connector for more details.
     *
     * @param array  $connectionData
     * @param array  $scopeData array('scope' => X, 'scope_id' => Y)
     * @throws Styla_Connect_Exception
     */
    public function updateConnectionConfiguration(array $connectionData, $scopeData)
    {
        $configuration = $this->getConfiguration();

        foreach ($this->_apiConfigurationFields as $fieldName => $configurationPath) {
            if (!isset($connectionData[$fieldName])) {
                continue; //not all data needs to be returned. we save whatever we can
            }

            $configuration->saveConfig(
                $configurationPath,
                $connectionData[$fieldName],
                $scopeData['scope'],
                $scopeData['scope_id']
            );
        }

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
        /** @var Styla_Connect_Model_Adminhtml_Config_Data $configModel */
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
     * @param        $scopeData
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

            $savedConfigurationValue = $this->getConfigurationNode(
                $configurationPathByMode,
                $scopeData['scope'],
                $scopeData['scope_id']
            );
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
     * @param mixed  $scope
     * @param mixed  $scopeId
     * @return null|string
     */
    public function getConfigurationNode($path = null, $scope = '', $scopeId = null)
    {
        $configuration = $this->getConfiguration();
        $value         = $configuration->getNode($path, $scope, $scopeId);

        return $value instanceof Mage_Core_Model_Config_Element ? $value->asArray() : null;
    }

    /**
     * Returns false if no configuration found for current mode
     *
     * @deprecated since version 0.1.1.6 as there's no modes anymore, there's no need to cache the connection data
     * @return bool|array
     */
    public function getCachedConnectionData($mode = null, $scopeData = null)
    {
        return false;
    }

    /**
     * Store the response from the Styla API Connector for the $moduleMode operation mode, so we don't have to call
     * styla again to get the same data
     *
     * @deprecated since version 0.1.1.6 as there's no modes anymore, there's no need to cache the connection data
     * 
     * @param array    $connectionData
     * @param          $moduleMode
     * @throws Styla_Connect_Exception
     */
    public function cacheConnectionData(array $connectionData, $moduleMode, $scopeData)
    {
        return;
    }
    
    /**
     * Get the configuration of a single field, within the given scope
     * 
     * @param string $fieldConfigurationPath
     * @param mixed $website
     * @param mixed $store
     * @return string|null
     */
    public function getFieldConfiguration($fieldConfigurationPath, $website = null, $store = null)
    {
        $scopeModel         = $this->resolveScope($website, $store);
        $scope              = $scopeModel->getScope();
        $scopeId            = $scopeModel->getScopeId();
        
        $node = $this->getConfigurationNode($fieldConfigurationPath, $scope, $scopeId);
        return $node;
    }
}