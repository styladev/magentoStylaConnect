<?php

/**
 * Class Styla_Connect_Model_Page
 *
 * @author ecocode GmbH <jk@ecocode.de>
 * @author Justus Krapp <jk@ecocode.de>
 */
class Styla_Connect_Model_Page
    extends Varien_Object
{
    const JAVASCRIPT_URL = 'https://%s.styla.com/scripts/clients/%s.js?v=%s';
    const CSS_URL        = 'https://%s.styla.com/styles/clients/%s.css?v=%s';

    const URL_CDN_PREFIX_PRODUCTION = 'cdn';
    const URL_CDN_PREFIX_STAGE      = 'dev';

    protected $_urlCdnPrefix = array(
        Styla_Connect_Helper_Config::MODE_PRODUCTION => self::URL_CDN_PREFIX_PRODUCTION,
        Styla_Connect_Helper_Config::MODE_STAGE      => self::URL_CDN_PREFIX_STAGE,
    );

    protected $tags;
    protected $baseTags;
    protected $_username;
    protected $_apiVersion;

    public function save()
    {
        throw new Exception('save is not supported!');
    }

    public function load($path)
    {
        $data = $this->_getApi()
            ->requestPageData($path);

        if ($data !== false) {
            $this->setData($data);
            $this->setData('exist', true);
        } else {
            $this->setData('exist', false);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function exist()
    {
        return $this->getData('exist') ? true : false;
    }

    /**
     * @return array
     */
    public function getBaseMetaData()
    {
        if (!$this->baseTags) {
            $tags = array(
                'title'       => $this->getTitle(),
                'description' => $this->getMetaDescription(),
                'keywords'    => $this->getMetaKeywords(),
                'robots'      => $this->getMetaRobots(),
            );

            $this->baseTags = array_filter($tags);
        }

        return $this->baseTags;
    }

    /**
     * @return array
     */
    public function getAdditionalMetaTags()
    {
        return array_diff_key(
            $this->getTags(),
            $this->getBaseMetaData()
        );
    }

    /**
     * @return array
     */
    public function getTags()
    {
        if (!$this->tags) {
            $this->tags = array();
            $tags       = $this->getData('tags');

            if (!$tags) {
                $tags = array();
            }

            foreach ($tags as $data) {
                $tagName = $data['tag'];

                $added = false;
                foreach (array('name', 'property') as $key) {
                    if (isset($data['attributes'][$key])) {
                        $added = true;
                        $this->addTag($tagName . '-' . $data['attributes'][$key], $data);
                    }
                }

                if (!$added) {
                    $this->tags[$tagName][] = $data;
                }

            }
        }

        return $this->tags;
    }

    public function addTag($name, $data)
    {
        if (!isset($this->tags[$name])) {
            $this->tags[$name] = array();
        }
        $this->tags[$name][] = $data;

        return $this;
    }

    /**
     * @param $type
     * @return array
     */
    public function getTag($type)
    {
        $tags = $this->getTags();
        if (isset($tags[$type])) {
            return $tags[$type];
        }

        return array();
    }

    /**
     * @param            $type
     * @param mixed      $default
     * @return mixed
     */
    public function getSingleContentTag($type, $default = false)
    {
        $tag = $this->getTag($type);

        if ($tag) {
            $tag = reset($tag);

            if (isset($tag['content']) && $tag['content']) {
                return $tag['content'];
            }

            if (isset($tag['attributes'], $tag['attributes']['content'])
                && $tag['attributes']['content']
            ) {
                return $tag['attributes']['content'];
            }
        }

        return $default;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getSingleContentTag('title', '');
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->getSingleContentTag('meta-description', '');
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->getSingleContentTag('meta-keywords', '');
    }

    /**
     * @return string
     */
    public function getMetaRobots()
    {
        return $this->getSingleContentTag('meta-robots', '');
    }

    public function getNoScript()
    {
        $html = $this->getData('html');

        return isset($html['body']) ? $html['body'] : '';
    }

    /**
     * no multi-language support yet so just take the config value
     *
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->getConfigHelper()->getLanguageCode();
    }

    /**
     *
     * @return string
     */
    public function getCssUrl()
    {
        $cssUrl     = self::CSS_URL;
        $repository = $this->getContentRepositoryPrefix();
        $clientName = $this->getUsername();
        $apiVersion = $this->getCurrentApiVersion();

        $cssUrl = sprintf($cssUrl, $repository, $clientName, $apiVersion);
        return $cssUrl;
    }

    /**
     * The CDN used to download the js/css has a different domain,
     * depending on the current mode of operation (stage/prod).
     * This method returns this prefix.
     *
     * @return string
     */
    public function getContentRepositoryPrefix()
    {
        $mode = $this->getConfigHelper()->getCurrentMode();

        $prefix = isset($this->_urlCdnPrefix[$mode]) ? $this->_urlCdnPrefix[$mode] : self::URL_CDN_PREFIX_STAGE;
        return $prefix;
    }

    /**
     * Get Styla client name
     *
     * @return string
     */
    public function getUsername()
    {
        if (null === $this->_username) {
            $this->_username = $this->getConfigHelper()->getUsername();
        }

        return $this->_username;
    }

    /**
     * Get the current url for Styla's JS script, used for loading the magazine page
     *
     * @return string
     */
    public function getScriptUrl()
    {
        $scriptUrl  = self::JAVASCRIPT_URL;
        $repository = $this->getContentRepositoryPrefix();
        $clientName = $this->getUsername();
        $apiVersion = $this->getCurrentApiVersion();

        $scriptUrl = sprintf($scriptUrl, $repository, $clientName, $apiVersion);

        return $scriptUrl;
    }

    /**
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
     *
     * @return Styla_Connect_Helper_Config
     */
    public function getConfigHelper()
    {
        return Mage::helper('styla_connect/config');
    }

    /**
     * @return Styla_Connect_Model_Styla_Api
     */
    protected function _getApi()
    {
        return Mage::getSingleton('styla_connect/styla_api');
    }
}
