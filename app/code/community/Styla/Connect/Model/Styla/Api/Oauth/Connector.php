<?php

/**
 * Class Styla_Connect_Model_Styla_Api_Oauth_Connector
 */
class Styla_Connect_Model_Styla_Api_Oauth_Connector
{
    const ADMIN_USERNAME                     = 'StylaApiAdminUser';
    const ADMIN_EMAIL_PREPEND                = 'stylaapiadmin.';
    const API2_ROLE_NAME                     = 'StylaApi2Role';
    const CONSUMER_NAME                      = 'Styla Api Connector';
    const REST_USER_TYPE                     = 'admin';
    const STYLA_API_CONNECTOR_URL_PRODUCTION = 'http://live.styla.com/api/magento';

    protected $_stylaLoginData;

    /**
     * Get the URL for connecting with Styla, by module's operating mode.
     * In development mode, the admin can force a url (any url) of his choice.
     *
     * @return string
     * @throws Exception
     */
    public function getConnectorApiUrl()
    {
        $connectionUrl = self::STYLA_API_CONNECTOR_URL_PRODUCTION;

        if(Mage::helper('styla_connect/config')->isDeveloperMode() && $forcedUrl = Mage::app()->getRequest()->getParam('connection_url')) {
            //do some basic validation on the url given by the admin
            if(filter_var($forcedUrl, FILTER_VALIDATE_URL) === false) {
                throw new Styla_Connect_Exception('The Connection URL you provided is invalid.');
            }

            $connectionUrl = $forcedUrl;
        }

        return $connectionUrl;
    }

    /**
     *
     * @return array
     */
    public function getStylaLoginData()
    {
        return $this->_stylaLoginData;
    }

    /**
     *
     * @param array $formData
     * @param mixed $defaultScope
     * @return boolean|array
     */
    protected function _getConnectionScope($formData, $defaultScope = null)
    {
        $scope   = is_array($defaultScope) && isset($defaultScope['scope']) ? $defaultScope['scope'] : null;
        $scopeId = is_array($defaultScope) && isset($defaultScope['scope_id']) ? $defaultScope['scope_id'] : null;
        if ($scope !== null && $scopeId !== null) {
            return array('scope' => $scope, 'scope_id' => $scopeId);
        }

        if (!isset($formData['scope'])) {
            return false;
        }

        $formScope = $formData['scope'];
        $formScope = explode('_', $formScope);

        $website = $formScope[0] !== 'default' ? $formScope[0] : null;
        $store   = isset($formScope[1]) ? $formScope[1] : null;

        if ($website === 'website') {
            $website = $store;
            $store   = null;
        }

        /**
         * normally, the form data will give us a scope like: "store_french" or "default".
         * we need to translate it into the proper magento scope for saving the data
         */
        $configModel = Mage::getSingleton('styla_connect/adminhtml_config_data');
        $configModel->setWebsite($website);
        $configModel->setStore($store);
        $configModel->resolveScope();

        return array('scope' => $configModel->getScope(), 'scope_id' => $configModel->getScopeId());
    }

    /**
     * Use this method to grant api2 access to Styla in your local magento installation.
     * This will create a special admin user, grant it all required attributes,
     * create the consumer and permanent token for this new user and send this data to Styla.
     *
     * @param array  $connectionFormData
     * @param bool   $forceSendingDataToStylaApi Should the connection data be always sent to Styla, even if cached locally
     * @param null   $scopeData
     * @throws Exception
     * @throws Styla_Connect_Exception
     */
    public function grantStylaApiAccess(
        array $connectionFormData,
        $forceSendingDataToStylaApi = false,
        $scopeData = null
    )
    {
        $this->_stylaLoginData = $connectionFormData;

        $connectionScope = $this->_getConnectionScope($connectionFormData, $scopeData);
        if (!$connectionScope) {
            throw new Exception(
                "Couldn't determine the scope for your connection."
            );
        }

        $adminUser = $this->getAdminUser();
        if (!$adminUser) {
            throw new Exception(
                "Couldn't create an API admin user for you. Please create the user manually, first (refer to the docs for details)."
            );
        }

        //update admin attributes, so that styla stuff is available
        $this->addStylaAttributesToAdminRole();

        $consumer = $this->getConsumer();

        $token = Mage::getModel('oauth/token')->getCollection()
            ->addFieldToFilter('consumer_id', $consumer->getId());
        $token = $token->getFirstItem();
        if (!$token->getId()) {
            $token = Mage::getModel('oauth/token')->createRequestToken($consumer->getId(), $this->getConnectorApiUrl());
        }

        //if this is a new token, it will be authorized and converted to permanent
        if (!$token->getAuthorized()) {
            $token->authorize($adminUser->getUserId(), Mage_Oauth_Model_Token::USER_TYPE_ADMIN);
            $token->convertToAccess();
        }

        $connectionData = $this->sendRegistrationRequest(
            $connectionFormData,
            $consumer,
            $token,
            $connectionScope
        );

        Mage::helper('styla_connect/config')->updateConnectionConfiguration($connectionData, $connectionScope);

        Mage::getSingleton('adminhtml/session')->addSuccess('Connection to Styla made successfully.');
    }

    /**
     * Use this method to get the module to re-save it's configuration to the one matching the current operation mode (stage/ prod).
     * It will try using a cached Styla connection API response.
     *
     * @deprecated after 0.1.1.4 because there's now separate fields for configuration, per module operation mode
     * @return boolean
     */
    public function tryUpdatingStylaAccessConfiguration()
    {
        return false;
    }

    /**
     *
     * @deprecated since version 0.1.1.6
     * @return stdClass|bool
     */
    public function getCachedConnectionData($mode = null, $scopeData = null)
    {
        return false;
    }

    /**
     *
     * @deprecated since version 0.1.1.6
     */
    public function cacheConnectionData(array $connectionData, $moduleMode, $scopeData)
    {
        return;
    }

    /**
     * Send the registration data to Styla and request module configuration
     *
     * @param array                     $loginData
     * @param Mage_Oauth_Model_Consumer $consumer
     * @param Mage_Oauth_Model_Token    $token
     * @param                           $scopeData
     * @return stdClass
     * @throws Exception
     * @throws Styla_Connect_Exception
     */
    public function sendRegistrationRequest($loginData, $consumer, $token, $scopeData)
    {
        //at this point we have all the login data we need for styla to access our api
        $stylaApi = Mage::getSingleton('styla_connect/styla_api');

        //make the api request to styla api
        $apiRequest = $stylaApi->getRequest(Styla_Connect_Model_Styla_Api::REQUEST_TYPE_REGISTER_MAGENTO_API);
        $apiRequest->setConnectionType(Zend_Http_Client::POST);
        $apiRequest->setParams(
            array(
                'styla_email'     => $loginData['email'],
                'styla_password'  => $loginData['password'],
                'consumer_key'    => $consumer->getKey(),
                'consumer_secret' => $consumer->getSecret(),
                'token_key'       => $token->getToken(),
                'token_secret'    => $token->getSecret(),
            )
        );

        $apiResponse = $stylaApi->callService($apiRequest, false);
        if (!$apiResponse->isOk()) {
            throw new Exception(
                "Couldn't connect to Styla API. Error result: " . $apiResponse->getHttpStatus()
                . ($apiResponse->getError() ? ' - ' . $apiResponse->getError() : '')
            );
        }

        //setup the api urls for this client
        /** @var array $connectionData */
        $connectionData = $apiResponse->getResult();

        return $connectionData;
    }

    /**
     *
     * @return Mage_Core_Model_Config
     */
    public function getConfiguration()
    {
        return new Mage_Core_Model_Config();
    }

    /**
     * Get/create a consumer intended for Styla API
     *
     * @return Mage_Oauth_Model_Consumer
     */
    public function getConsumer()
    {
        /** @var Mage_Oauth_Model_Consumer $consumer */
        $consumer  = Mage::getModel('oauth/consumer');
        $consumers = $consumer->getCollection()
            ->addFieldToFilter('name', self::CONSUMER_NAME);

        $consumer = $consumers->getFirstItem();
        if (!$consumer->getId()) {
            //create new consumer
            $helper = Mage::helper('oauth');
            $consumer->setKey($helper->generateConsumerKey());
            $consumer->setSecret($helper->generateConsumerSecret());
            $consumer->setName(self::CONSUMER_NAME);

            $consumer->save();
        }

        return $consumer;
    }

    /**
     * Get/create a special-purpose admin user.
     * This user will connect to Styla api.
     *
     * @param bool $createIfNotExist
     * @return Mage_Admin_Model_User
     * @throws Exception
     */
    public function getAdminUser($createIfNotExist = true)
    {
        $adminUsers = Mage::getModel('admin/user')->getCollection()
            ->addFieldToFilter('username', self::ADMIN_USERNAME);

        $adminUser = $adminUsers->getFirstItem();
        if (!$adminUser->getId() && $createIfNotExist) {
            $stylaLoginData = $this->getStylaLoginData();

            /**
             * the admin email needs to be unique, so we'll take user's email and prepend to it, in case
             * the same email is already used as magento admin
             */
            $adminEmail = self::ADMIN_EMAIL_PREPEND . $stylaLoginData['email'];

            //create a new admin user for Styla
            $adminUser->setUsername(self::ADMIN_USERNAME)
                ->setFirstname('Styla')
                ->setLastname('Api Connector')
                ->setEmail($adminEmail)
                ->setPassword($stylaLoginData['password'])
                ->save();

            //set admin role for this new user
            $role = Mage::getModel('admin/role');
            $role->setParent_id(1);
            $role->setTree_level(1);
            $role->setRole_type('U');
            $role->setUser_id($adminUser->getId());
            $role->save();

            //assign this user to API2 role
            $this->assignAdminUserToApi2Role($adminUser);
        }

        return $adminUser->getId() ? $adminUser : false;
    }

    /**
     * Certain attributes must be allowed for the admin role, in order for styla api to operate
     */
    public function addStylaAttributesToAdminRole()
    {
        /*
         * check if the admin user already has "all" attributes, and if he does - skip
         */
        if ($this->adminUserHasAllAttributes()) {
            return;
        }

        $this->resetStylaAttributesInAdminRole();

        $attributesToUse = $this->getAttributesForStyla();
        foreach ($attributesToUse as $group => $attributes) {
            /** @var $attribute Mage_Api2_Model_Acl_Filter_Attribute */
            $attribute = Mage::getModel('api2/acl_filter_attribute');

            $attribute->setData(
                array(
                    'user_type'          => self::REST_USER_TYPE,
                    'resource_id'        => $group,
                    'operation'          => 'read', //we're only using read operation
                    'allowed_attributes' => $attributes,
                )
            );

            $attribute->save();
        }
    }

    /**
     * Check if the admin user already has "all" ACL attributes assigned
     *
     * @return bool
     */
    public function adminUserHasAllAttributes()
    {
        /** @var $collection Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection */
        $collection = Mage::getModel('api2/acl_filter_attribute')->getCollection();
        $collection->addFilterByUserType(self::REST_USER_TYPE);
        $collection->addFieldToFilter('resource_id', 'all');

        $firstAttribute = $collection->getFirstItem();

        return $firstAttribute->getId() ? true : false;
    }

    /**
     * If we have existing attributes assigned to our admin user, delete them all
     *
     */
    public function resetStylaAttributesInAdminRole()
    {
        /** @var $collection Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection */
        $collection = Mage::getModel('api2/acl_filter_attribute')->getCollection();
        $collection->addFilterByUserType(self::REST_USER_TYPE);
        $collection->addFieldToFilter('resource_id', array('in' => array('styla_product', 'styla_category')));

        foreach ($collection as $item) {
            $item->delete();
        }

        return $this;
    }

    /**
     * Will return all attributes used in this version of the Connect module
     *
     * @return array
     */
    public function getAttributesForStyla()
    {
        $fieldConfiguration = Mage::getConfig()
            ->loadModulesConfiguration('api2.xml')
            ->getNode('api2/resources')->asArray();

        $attributes = array(
            'styla_category' => implode(',', array_keys($fieldConfiguration['styla_category']['attributes'])),
            'styla_product'  => implode(',', array_keys($fieldConfiguration['styla_product']['attributes'])),
        );

        return $attributes;
    }

    /**
     * Assign an admin user to a api2 role (create the role if it's missing)
     *
     * NOTE: this role allows you to view the catalog. This is defined by the 'resource' role parameter below.
     *
     * @param Mage_Admin_Model_User $adminUser
     */
    public function assignAdminUserToApi2Role($adminUser)
    {
        $roleData = array(
            'in_role_users' => array($adminUser->getId()),
            'role_name'     => self::API2_ROLE_NAME,
            'resource'      => '__root__,group-catalog,resource-styla_category,privilege-styla_category-retrieve,resource-styla_product,privilege-styla_product-retrieve',
            'all'           => '0',
        );

        //a little trick - mage implementation needs these params to be in POST....
        foreach ($roleData as $key => $value) {
            Mage::app()->getRequest()->setPost($key, $value);
        }

        $role = Mage::getModel('api2/acl_global_role');

        $roles        = $role->getCollection()
            ->addFieldToFilter('role_name', $roleData['role_name']);
        $existingRole = $roles->getFirstItem();
        if ($existingRole->getId()) {
            $role = $existingRole;
        } else {
            //create the new role
            $role->setRoleName($roleData['role_name'])->save();
        }

        foreach ($roleData['in_role_users'] as $roleUser) {
            $this->_addUserToRole($roleUser, $role->getId());
        }

        /** @var $rule Mage_Api2_Model_Acl_Global_Rule */
        $rule = Mage::getModel('api2/acl_global_rule');

        //save API2 access rules
        /** @var $ruleTree Mage_Api2_Model_Acl_Global_Rule_Tree */
        $ruleTree  = Mage::getSingleton(
            'api2/acl_global_rule_tree',
            array('type' => Mage_Api2_Model_Acl_Global_Rule_Tree::TYPE_PRIVILEGE)
        );
        $resources = $ruleTree->getPostResources();
        $id        = $role->getId();
        foreach ($resources as $resourceId => $privileges) {
            foreach ($privileges as $privilege => $allow) {
                if (!$allow) {
                    continue;
                }

                $rule->setId(null)
                    ->isObjectNew(true);

                $rule->setRoleId($id)
                    ->setResourceId($resourceId)
                    ->setPrivilege($privilege)
                    ->save();
            }
        }
    }

    /**
     * Give user a role
     *
     * @param int $adminId
     * @param int $roleId
     * @return Mage_Api2_Adminhtml_Api2_RoleController
     */
    protected function _addUserToRole($adminId, $roleId)
    {
        /** @var $resourceModel Mage_Api2_Model_Resource_Acl_Global_Role */
        $resourceModel = Mage::getResourceModel('api2/acl_global_role');
        $resourceModel->saveAdminToRoleRelation($adminId, $roleId);

        return $this;
    }
}
