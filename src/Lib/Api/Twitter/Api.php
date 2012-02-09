<?php
/**
 * Lib_Twitter_Twitter Class
 *
 * @category	basilicom
 * @author      Manuel Banz
 * @package		Lib_Twitter
 *
 * @copyright	Copyright (c) 2011 basilicom GmbH (http://basilicom.de)
 * @license		http://basilicom.de/license/default
 * @version		$Id$
 *
 */

/**
 * Lib_Twitter_Twitter
 *
 * A wrapper for the Twitter API.
 *
 * @category	basilicom
 * @author      Manuel Banz
 * @package		Lib_Twitter
 *
 * @copyright	Copyright (c) 2011 basilicom GmbH (http://basilicom.de)
 * @license		http://basilicom.de/license/default
 * @version     $Id$
 *
 */
 
class Lib_Api_Twitter_Api extends Zend_Service_Twitter
{

     /**
     * Public Timeline status
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return array
     */
    public function statusPublicTimeline()
    {
        $this->_init();
        $path = '/1/statuses/public_timeline.json';
        $response = $this->_get($path);
        return json_decode($response->getBody());
    }

    /**
     * User Followers
     *
     * @param  bool $lite If true,
     * prevents inline inclusion of current status for followers;
     * defaults to false
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return array
     */
    public function userFollowers($lite = false)
    {
        $this->_init();
        $path = '/1/statuses/followers.json';
        if ($lite) {
            $this->lite = 'true';
        }
        $response = $this->_get($path);
        return json_decode($response->getBody());
    }

    /**
     * Verify Account Credentials
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     *
     * @return array
     */
    public function accountVerifyCredentials()
    {
        $this->_init();
        $response = $this->_get('/1/account/verify_credentials.json');
        return json_decode($response->getBody());
    }

    /**
     * Get Account Totals
     * eg:
       "friends": 360,
       "followers": 6240,
       "updates": 3835,
       "favorites": 120
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     *
     * @return array
     */
    public function accountTotals()
    {
        $this->_init();
        $response = $this->_get('/1/account/totals.json');
        return json_decode($response->getBody());
    }

    /**
     * Get status replies
     *
     * $params may include one or more of the following keys
     * - since_id: return results only after the specified tweet id
     * - page: return page X of results
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @param $params array
     * @return array
     */
    public function statusReplies(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/mentions.json';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->_get($path, $_params);
        return json_decode($response->getBody());
    }
}
