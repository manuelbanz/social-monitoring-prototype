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

class App_Source_Mailchimp extends Fetcher_Source_Abstract
{

    const NEWSLETTER_LIST_GER = 'Sportalm Newsletter';
    const NEWSLETTER_LIST_ENG = 'Sportalm Newsletter International';

    const FOLDER_ID_GER = 23477;
    const FOLDER_ID_ENG = 23481;

    /**
     * Initialize this source object with source information
     * @param  $sourceId
     */
    public function __construct($sourceId)
    {
        $this->setDbId($sourceId);
        $sourceInformation = $this->getSourceInformationFromDb();
        $this->setSourceInformation($sourceInformation);
    }

    /**
     * @param string $listName
     * @param Lib_Mailchimp_MailchimpApi $mailchimpApi
     * @return int
     */
    private function getSubscriber($listName, $mailchimpApi)
    {
        //get all mailchimp lists
        $lists = $mailchimpApi->listLists();

        //get subscriber for the list
        foreach ($lists['data'] as $list) {
            if ($list['name'] == $listName) {
                return $list['stats']['member_count'];
            }
        }

        return null;
    }

    /**
     * Fetches all new monitoring data from web of the specific source.
     * @return array
     */
    public function getNewMonitoringData()
    {
        $monitoringData = array();
        $auth = $this->getAuthenticationData();
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $mailchimpApi = new Lib_Api_Mailchimp_Api($auth);

        if ($mailchimpApi != null) {

            //get subscriber stats:
            $monitoringData['subscriber']['subscriberGer'] = $this->getSubscriber(self::NEWSLETTER_LIST_GER, $mailchimpApi);
            $monitoringData['subscriber']['subscriberEng'] = $this->getSubscriber(self::NEWSLETTER_LIST_ENG, $mailchimpApi);


            //get all mailchimp campaigns
            $campaigns = $mailchimpApi->listCampaigns();

            $campaignIds = array();
            foreach ($campaigns['data'] as $campaign) {
                $folderId = trim($campaign['folder_id']);
                if ($folderId == self::FOLDER_ID_ENG OR $folderId == self::FOLDER_ID_GER) {
                    //$campaignIds[$campaign['send_time']] = $campaign['title'];
                    $campaignIds[$campaign['id']] = array(
                        'send_time' => $campaign['send_time'],
                        'title' => $campaign['title'],
                    );
                }
            }

            if (!empty($campaignIds)) {
                $select = "SELECT Source.label
                       FROM Source
                       WHERE Source.sourceTypeId = 4";

                $newsletterSourcesDb = $dbObject->fetchAll($select);

                $dbId = $this->getDbId();
                $select = "SELECT Source.label
                            FROM Source
                            WHERE Source.id = $dbId";

                $currentSource = $dbObject->fetchRow($select);

                foreach ($newsletterSourcesDb as $newsletterSource) {
                    $newsletterSources[] = $newsletterSource['label'];
                }

                $currentCampaignId = null;
                //create new source if new newsletter is found
                foreach ($campaignIds as $campaignId => $campaignInfo) {

                    if ($currentSource['label'] == $campaignInfo['title']) {
                        $currentCampaignId = $campaignId;
                    }

                    if (!in_array($campaignInfo['title'], $newsletterSources)) {
                        $dbObject->insert(
                            'Source',
                            array(
                                 'sourceTypeId' => 4,
                                 'authId' => 10,
                                 'label' => $campaignInfo['title'],
                                 'active' => 1
                            )
                        );
                    }

                }

                foreach ($campaignIds as $campaignInfo) {
                    //set campaign as event
                    $this->setCampaignAsEvent(
                        $campaignInfo['send_time'],
                        $campaignInfo['title']
                    );
                }
                
                //get camapaign stats
                $monitoringData['campaignStats'] = $mailchimpApi->getCampaignStats($currentCampaignId);


                //get click stats
                $clickStats = $mailchimpApi->getClickStats($currentCampaignId);

                $monitoringData['clickStats'] = $this->changeClickStatsArray($clickStats);
            } else {
                $outputInfo = "\nno campaigns found in the specified folder! see piwik_socmon_config.php or " .
                              "authentication data in db!\n";
                echo $outputInfo;
                /** @var $logger Zend_Log */
                $logger = Zend_Registry::get('LOG');
                $logger->log($outputInfo, Zend_Log::ALERT);
            }
        } else {
            $outputInfo = "\ncould not get some monitoring data. check the "
                          . "authentication data in db!\n";
            echo $outputInfo;
            /** @var $logger Zend_Log */
            $logger = Zend_Registry::get('LOG');
            $logger->log($outputInfo, Zend_Log::ALERT);
        }

        return $monitoringData;
    }



    /**
     * @param $lastCampaignDate
     * @param $campaignTitle
     * @return void
     */
    private function setCampaignAsEvent($lastCampaignDate, $campaignTitle)
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $campaignDate = substr($lastCampaignDate, 0, 10);

        $sqlSelect = "SELECT Event.label, Event.startDate
                              FROM Event
                              WHERE Event.startDate = '$campaignDate'
                              AND Event.label = '$campaignTitle'";

        try {
            $dbResult = $dbObject->fetchRow($sqlSelect);
            if (empty($dbResult)) {
                $dbObject->insert(
                    'Event',
                    array(
                         'sourceTypeId' => '4',
                         'label' => $campaignTitle,
                         'startDate' => substr($lastCampaignDate, 0, 10),
                         'endDate' => substr($lastCampaignDate, 0, 10),
                         'campaignId' => 1
                    )
                );
            }

        } catch (Exception $err) {
            echo "sql error: " . $err->getMessage();
        }
    }

    /**
     * @param $clickStats
     * @return array
     */
    private function changeClickStatsArray($clickStats)
    {
        $urlClicks = array();
        $urlUnqiueClicks = array();

        if (is_array($clickStats)) {
            foreach ($clickStats as $url => $clicks) {
                $urlClicks[$url] = $clicks['clicks'];
                $urlUnqiueClicks[$url] = $clicks['unique'];
            }

            $clickStats = array();
            $clickStats['urlClicks'] = $urlClicks;
            $clickStats['urlUniqueClicks'] = $urlUnqiueClicks;
        }

        return $clickStats;
    }

    /**
     * Fetches all new monitoring data from web of the specific source.
     * DEPRECATED
     * @return array
     */
    public function getNewMonitoringData_DEPRECATED()
    {
        $monitoringData = array();
        $auth = $this->getAuthenticationData();
        $folderName = $auth->objectId;
        $mailchimpApi = new Lib_Mailchimp_MailchimpApi($auth);

        if ($mailchimpApi != null) {

            //get subscriber stats:
            $monitoringData['subscriber']['subscriberGer'] = $this->getSubscriber(self::NEWSLETTER_LIST_GER, $mailchimpApi);
            $monitoringData['subscriber']['subscriberEng'] = $this->getSubscriber(self::NEWSLETTER_LIST_ENG, $mailchimpApi);

            //get all folders and search folder id for current folder from db
            $folders = $mailchimpApi->getFolders();

            $folderId = null;
            foreach ($folders as $folder) {
                if ((trim($folder['name']) == trim($folderName))
                    AND $folder['type'] == 'campaign'
                ) {
                    $folderId = $folder['folder_id'];
                }
            }

            if (!is_float($folderId)) {
                $campaignId = null;
            } else {
                //get all campaigns from the folder and get the campaign id of the last campaign
                $campaigns = $mailchimpApi->listCampaigns();
                foreach ($campaigns['data'] as $campaign) {
                    if (trim($campaign['folder_id']) == trim($folderId)) {
                        $campaignId = $campaign['id'];
                        $lastCampaignDate = $campaign['send_time'];
                        $campaignTitle = $campaign['title'];
                        break; //take the newest campaign
                    }
                }
            }

            if (!empty($campaignId) AND !empty($lastCampaignDate)) {
                //get DB object
                /** @var $dbObject Zend_Db_Adapter_Abstract */
                $dbObject = Zend_Registry::get('DB');

                $folderName = trim($folderName);
                $select = "SELECT Source.id
                       FROM Source, Authentication
                       WHERE Authentication.id = Source.authId
                       AND Authentication.objectId = '$folderName'";

                try {
                    $source = $dbObject->fetchRow($select);
                } catch (Exception $err) {
                    echo "sql error: " . $err->getMessage();
                }

                $sourceId = $source['id'];

                try {
                    //set date for the newest campaign
                    $dbObject->update(
                        'Source',
                        array('lastDate' => $lastCampaignDate),
                        "id = $sourceId"
                    );
                } catch (Exception $err) {
                    echo "sql error: " . $err->getMessage();
                }


                //set campaign as event
                $this->setCampaignAsEvent($lastCampaignDate, $campaignTitle);

                //get camapaign stats
                $monitoringData['campaignStats'] = $mailchimpApi->getCampaignStats($campaignId);


                //get click stats
                $clickStats = $mailchimpApi->getClickStats($campaignId);

                $monitoringData['clickStats'] = $this->changeClickStatsArray($clickStats);

            } else {
                $outputInfo = "\nno campaigns found in the specified folder! see piwik_socmon_config.php or " .
                              "authentication data in db!\n";
                echo $outputInfo;
                /** @var $logger Zend_Log */
                $logger = Zend_Registry::get('LOG');
                $logger->log($outputInfo, Zend_Log::ALERT);
            }
        } else {
            $outputInfo = "\ncould not get some monitoring data. check the "
                          . "authentication data in db!\n";
            echo $outputInfo;
            /** @var $logger Zend_Log */
            $logger = Zend_Registry::get('LOG');
            $logger->log($outputInfo, Zend_Log::ALERT);
        }

        return $monitoringData;
    }

} //End class
