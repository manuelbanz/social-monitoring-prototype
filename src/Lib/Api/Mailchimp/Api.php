<?php
/**
 * MailchimpApi Class
 *
 * @category    basilicom
 * @author      Manuel Banz
 * @package     Lib_MailchimpApi
 *
 * @copyright   Copyright (c) 2011 basilicom GmbH (http://basilicom.de)
 * @license     http://basilicom.de/license/default
 * @version     $Id$
 *
 */

/**
 * MailchimpApi
 *
 * A wrapper for accessing the Mailchimp API.
 *
 * @category    basilicom
 * @author      Manuel Banz
 * @package     Lib_Mailchimp
 *
 * @copyright   Copyright (c) 2011 basilicom GmbH (http://basilicom.de)
 * @license     http://basilicom.de/license/default
 * @version     $Id$
 *
 */


class Lib_Api_Mailchimp_Api
{

    /**
     * User-provided configuration
     *
     * @var Zend_Config
     */
    protected $_config;

    /**
     * The campaign id.
     * @var String
     */
    protected $_objectId;

    /**
     * access token mailchimp
     * @var String
     */
    protected $_accessToken;

    /**
     * Mailchimp MCAPI object
     * @var MCAPI
     */
    protected $_mailchimpApi;

    public function __construct($config)
    {
        /*
         * Verify that adapter parameters are in an array.
         */
        if (is_array($config)) {
            $this->_config = new Zend_Config($config);
        } elseif ($config instanceof Zend_Config) {
            $this->_config = $config;
        } else {
            // empty config
            $this->_config = new Zend_Config(array());
        }
    }

    /**
     *
     * @return bool
     */
    private function _checkRequiredOptions()
    {
        $configError = array();

        /** @noinspection PhpUndefinedFieldInspection */
        $this->_objectId = $this->_config->objectId;
        $this->_accessToken = $this->_config->accessToken;

        if (empty($this->_objectId))
            $configError[] = "set campaignId";

        if (empty($this->_accessToken))
            $configError[] = "set accessToken";


        //error occurred:
        if (count($configError) > 0) {
            echo "\nmissing authentication data for mailchimp api: \n";
            foreach ($configError as $err) {
                echo '*  ' . $err . "\n";
            }
            return false;
        }
        //all authentication data set
        return true;
    }

    protected function getMcapi()
    {
        if ($this->_mailchimpApi instanceof Lib_Api_Mailchimp_ApiBase)
            return $this->_mailchimpApi;
        else if ($this->_checkRequiredOptions()) {
            return $this->_mailchimpApi =
                new Lib_Api_Mailchimp_ApiBase($this->_accessToken);
        } else {
            return null;
        }
    }

    public function getCampaignStats($campaignId)
    {
        $stats = null;
        $api = $this->getMcapi();
        if ($api != null) $stats = $api->campaignStats($campaignId);
        return $stats;
    }


    public function getClickStats($campaignId)
    {
        $clickStats = null;
        $api = $this->getMcapi();
        if ($api != null) $clickStats = $api->campaignClickStats($campaignId);
        return $clickStats;
    }

    public function listCampaigns($filters = array())
    {
        $campaigns = null;
        $api = $this->getMcapi();
        if ($api != null) $campaigns = $api->campaigns($filters);
        return $campaigns;
    }

    public function listLists()
    {
        $lists = null;
        $api = $this->getMcapi();
        if ($api != null) $lists = $api->lists();
        return $lists;
    }

    public function getFolders()
    {
        $folders = null;
        $api = $this->getMcapi();
        if ($api != null) $folders = $api->folders();
        return $folders;
    }

    public function getCampaignAnalytics()
    {
        $analytics = null;
        $api = $this->getMcapi();
        if ($api != null) $analytics = $api->campaignStats($this->_objectId);
        return $analytics;
    }
}