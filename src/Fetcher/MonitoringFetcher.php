<?php
/**
 * Class MonitoringFetcher
 *
 * task steps:
 *   - fetch all sources from db
 *   - retrieves the new monitoring data for each source
 *   - create data objects for data and sources
 *   - save the new data to the db
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher
 *
 */

class Fetcher_MonitoringFetcher
{
    /**
     * Starts the monitoring fetcher.
     */
    public function run()
    {
        //display ouput information
        $currentDate = new DateTime('now');
        $currentDateFormat = $currentDate->format('d.m.Y - G:i');
        $outputInfo =  "\n\n***Monitoring Fetcher***\n\n";
        $outputInfo .= "start! ";
        $outputInfo .= "$currentDateFormat \n\n";
        echo $outputInfo;

        # 1) get monitoring sources from db:
        $sourceFetcher = new Fetcher_SourceFetcher();
        $sourceList = $sourceFetcher->getSources();

        # 2) get new insights data from web for each source
        /** @var $source Fetcher_Source_SourceAbstract */
        foreach ($sourceList as $source) {
            $outputInfo = " \n\n\n==> get new monitoring data from source " .
                 $source->getDbId() . " (" . $source->getSourceType() . ") \n";
            echo $outputInfo;

            $monitoringData = $source->getNewMonitoringData();

            if (!$this->empty_r($monitoringData)) {
                if (isset($monitoringData['date'])) {
                    $date = $monitoringData['date'];
                    unset($monitoringData['date']);
                    $source->setAllMonitoringData($monitoringData, $date);
                } else {
                    $source->setAllMonitoringData($monitoringData);
                }
                # 3) save all insights data to database:
                $outputInfo = "\nsave source " . $source->getDbId() .
                     " to database...\n\n";
                echo $outputInfo;

                if (!$this->empty_r($source->getMonitoringDataList())) {
                    $source->saveMyMonitoringDataToDb();
                } else {
                    $outputInfo = "could not save something to db, " .
                         "no metrics found in db or api response was empty!\n";
                    echo $outputInfo;
                }
            } else {
                $outputInfo = "\ncould not fetch monitoring data for source " .
                     $source->getDbId() . " \n";
                echo $outputInfo;
            }
            echo "\n\n----------------------------------------------------------------------";
        }   

        $outputInfo = "\n\n***END Monitoring Fetcher***\n\n";
        echo $outputInfo;
    }

    /**
     * check if array (with subarrays) has no values
     * @param  $arr
     * @return bool
     */
    protected function empty_r($arr)
    {
        if (!is_array($arr)) {
            return true;
        }
        
        if (empty($arr) OR $arr == null) return true;

        foreach ($arr as $subArr) {
            if (empty($subArr)) return true;
        }

        return false;
    }


    /**
     * Getopt function
     * Generates a hyperlink which has to be entered in browser and
     * returns an access token for the twitter api.
     * @return void
     */
    protected function generateTwitterToken()
    {
        $authData = array(
            'siteUrl' => 'https://twitter.com/oauth',
            'consumerKey' => '',
            'consumerSecret' => ''
        );
        $consumer = new Zend_Oauth_Consumer($authData);

        $requestToken =
                $consumer->getRequestToken(array('callbackUrl' => 'oob'));
        echo "\nOpen that URL in browser: \n
        {$consumer->getRedirectUrl()}" . PHP_EOL;

        fwrite(STDOUT, "Enter the PIN: ");
        $pin = trim(fgets(STDIN));
        try {
            $accessToken = $consumer->getAccessToken(
                array(
                     'oauth_verifier' => $pin,
                     'oauth_token' => $requestToken->getToken()
                ),
                $requestToken
            );
        } catch (Zend_Oauth_Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            exit;
        }
        echo "OAuth Token: {$accessToken->getToken()}" . PHP_EOL;
        echo "OAuth Secret: {$accessToken->getTokenSecret()}" . PHP_EOL;
        echo "Write this to the database and start MonitoringFetcher again";
        die();
    }

    /**
     * Getopt function
     * CAUTION!
     * Truncates the table 'Data'.
     * @return void
     */
    protected function truncateData()
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $sql = 'TRUNCATE TABLE Data';

        $dbObject->query($sql);

        echo "\nTruncated database! Start MonitoringFetcher again!\n\n";
        die();
    }

    /**
     * Getopt function
     * Adds a metric to the database which will be fetched.
     * @return void
     */
    protected function addMetric()
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $gk = $this->_metric;

        $insert = array(
            'sourceTypeId' => 1,
            'metricGroupKey' => $gk,
            'label' => 'Facebook Metric',
        );

        $dbObject->insert('MetricGroup', $insert);

        $select = "SELECT id FROM MetricGroup WHERE metricGroupKey = '$gk'";

        $groupId = $dbObject->fetchRow($select);

        $insert = array(
            'metricGroupId' => $groupId['id'],
            'metricKey' => $gk,
            'label' => 'Facebook Metric',
        );

        $dbObject->insert('Metric', $insert);
        echo "\nAdded metric $gk to the database\n";
        die();
    }

} // End class

