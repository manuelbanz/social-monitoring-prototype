<?php
/**
 * Class Mailchimp
 *
 * This class reads the authentication information for the given source from db.
 * Then new monitoring data is fetched via api requests and set to this object.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher_Source
 *
 */

class App_Source_Twitter extends Fetcher_Source_Abstract
{

    /**
     * Initialize this source object with source information
     * @param  $sourceId
     */
    public function __construct($sourceId)
    {
        $this->setDbId($sourceId);
        $sourceInformation = $this->getSourceInformationFromDb($sourceId);
        $this->setSourceInformation($sourceInformation);
    }

    /**
     * Fetches all new monitoring data from web of the specific source.
     * @return array
     */
    public function getNewMonitoringData()
    {
        $auth = $this->getAuthenticationData()->toArray();

        $oauthToken = $auth['objectId'];
        $oauthTokenSecret = $auth['accessToken'];

        $token = new Zend_Oauth_Token_Access();
        $token->setToken($oauthToken)
            ->setTokenSecret($oauthTokenSecret);

        $twitter = new Lib_Api_Twitter_Api(
            array('username' => 'sportalm_kb',
                 'accessToken' => $token)
        );

        //        print_r($twitter->statusPublicTimeline());
        //        print_r($twitter->userFollowers());

        //        $monitoringData['account/verify_credentials'] =
        //            $twitter->accountVerifyCredentials();

        $monitoringData['account/totals'] =
            $this->object2array($twitter->accountTotals());

        // $monitoringData['statuses/mentions'] = $twitter->statusReplies();

        return $monitoringData;
    }

    protected function object2array($object)
    {
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                $array[$key] = $value;
            }
        } else {
            $array = $object;
        }
        return $array;
    }
}
