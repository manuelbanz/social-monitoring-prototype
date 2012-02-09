<?php
/**
 * PageInsights Class
 *
 * @category    basilicom
 * @package     Lib_Facebook
 *
 * @copyright   Copyright (c) 2011 basilicom GmbH (http://basilicom.de)
 * @license     http://basilicom.de/license/default
 * @version     $Id$
 *
 */

/**
 * GraphApi
 *
 * Class for doing request to the graph api. A config file has to be set.
 *
 * @category    basilicom
 * @package     Lib_Facebook
 *
 * @copyright   Copyright (c) 2011 basilicom GmbH (http://basilicom.de)
 * @license     http://basilicom.de/license/default
 * @version     $Id$
 *
 */


class Lib_Api_Facebook_GraphApi
{
    /**
     * User-provided configuration
     *
     * @var Zend_Config
     */
    protected $_config;

    /**
     * The facebook id of your facebook page.
     * @var String
     */
    protected $_objectId;

    /**
     * generated offline access token
     * @var String
     */
    protected $_accessToken;

    /**
     * Constructor.
     *
     * $config is an array of key/value pairs or an instance of Zend_Config
     * containing configuration options.
     *
     * @param  array|Zend_Config $config
     * An array or instance of Zend_Config having configuration data
     */
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
        //        $this->_redirectUrl = $this->_config->redirectUrl;

        if (empty($this->_objectId))
            $configError[] = "set pageId";

        if (empty($this->_accessToken))
            $configError[] = "set accessToken";


        //error occurred:
        if (count($configError) > 0) {
            echo "\nmissing authentication data for facebook page insights: \n";
            foreach ($configError as $err) {
                echo '*  ' . $err . "\n";
            }
            return false;
        }
        //all authentication data set
        return true;
    }


    public function getInsights($startDate, $endDate = null)
    {
        return $this->apiRequest('insights', $startDate, $endDate);
    }

    public function getGeneralInfo($startDate, $endDate)
    {
        return $this->apiRequest('', $startDate, $endDate);
    }

    public function getFeed($startDate, $endDate)
    {
        return $this->apiRequest('feed', $startDate, $endDate);
    }

    /**
     * @param  $startDate
     * @param $endDate
     * @param string $type
     * (see https://developers.facebook.com/docs/reference/api/
     *  for other api calls; if type is empty general page information is called
     *  examples: /insights, /feed  /posts)
     * @return string
     */
    protected function apiRequest($type, $startDate, $endDate = null)
    {
        $insights = null;
        //all authentication data is set
        if ($this->_checkRequiredOptions()) {
            //$startDate->modify('+1 day');
            $path = $this->_objectId . '/' . $type;
            //eg. https://graph.facebook.com/ID/insights
            $url = $this->getGraphApiUrl($path);
            $params = array(
                'access_token' => $this->_accessToken,
                'method' => 'GET',
//                'since' => $startDate->format('Y-m-d'),
                'since' => $startDate
            );
            if ($endDate != null) $params['until'] = $endDate;
            //$params['until'] = $endDate->format('Y-m-d');
            //$params['until'] = $endDate;
            try {
                $insights = $this->fetchUrl($url, $params);
            } catch (Exception $e) {
                print_r($e->getMessage());
            }
        }
        return $insights;
    }

    /**
     * Creates an url from host + path + Get-parameters.
     * @param  $httphost
     * @param  $path
     * @param  $params
     * @return string
     */
    private function getUrl($httphost, $path, $params)
    {
        $url = $httphost;
        if ($path) {
            if ($path[0] === ' / ') {
                $path = substr($path, 1);
            }
            $url .= $path;
        }
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    /**
     * Return a valid facebook graph url for given path and parameters.
     * @param string $path
     * @param array $params
     * @return string
     */
    public function getGraphApiUrl($path = '', $params = array())
    {
        return $this->getUrl('https://graph.facebook.com/', $path, $params);
    }

    /**
     * Return the insights data as decoded json.
     * @throws Exception
     * @param  $url
     * @param  $params
     * @return string
     */
    public function fetchUrl($url, $params)
    {
        $params['format'] = 'json-strings';
        $curleHandler = curl_init();
        //set curl options
        $opts = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            //CURLOPT_USERAGENT => 'facebook-php-2.0',
            CURLOPT_URL => $url,
        );
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
        curl_setopt_array($curleHandler, $opts);
        try {
            $result = curl_exec($curleHandler);
        } catch (Exception $error) {
            $outputInfo = "curl error: ";
            $outputInfo .= $error->getMessage();

            /** @var $logger Zend_Log */
            $logger = Zend_Registry::get('LOG');
            $logger->log($outputInfo, Zend_Log::ERR);
        }

        if ($result === false) {
            $exception = new Exception(
                curl_error($curleHandler),
                curl_errno($curleHandler)
            );
            curl_close($curleHandler);
            throw $exception;
        }
        curl_close($curleHandler);
        return json_decode($result, true);
    }

} //End Class
