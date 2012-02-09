<?php
/**
 * Class Facebook
 *
 * This class reads the authentication information for the given source from db.
 * Then new monitoring data is fetched via api requests and set to this object.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher_Source
 *
 */

class Fetcher_Source_Facebook extends Fetcher_Source_Abstract
{

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
     * Fetches all new monitoring data from web of the specific source.
     * @return array
     */
    public function getNewMonitoringData()
    {
        $auth = $this->getAuthenticationData();
        $graphApi = new Lib_Api_Facebook_GraphApi($auth);
        if ($graphApi != null) {
            //get 2 days old monitoring data  (newest facebook statistics)
            $startDate = new DateTime();
            $startDate->modify('-2 day');
            $startDate = $startDate->format('Y-m-d');
            $monitoringData = array();
            $monitoringData['/insights'] = $graphApi->getInsights($startDate);

            if (!empty($monitoringData['/insights']['data'])) {
                $monitoringData = $this->changeMonitoringDataArray($monitoringData);
            }

            return $monitoringData;
        } else {
            $outputInfo = "\ncould not get some monitoring data. check the "
                 . "authentication data in db!";
            echo $outputInfo;

            /** @var $logger Zend_Log */
            $logger = Zend_Registry::get('LOG');
            $logger->log($outputInfo, Zend_Log::ALERT);

            return null;
        }
    }

    /**
     * takes the facebook monitoring array and creates a new simple key-value-
     * array (so i can use the setAllMonitoringData function in SourceAbstract)
     * @param $monitoringData
     * @return array
     */
    protected function changeMonitoringDataArray($monitoringData)
    {
        $newMonitoringData = $monitoringData;
        $newInsightsArray = array();
        // get date of the facebook monitoring data (just from the first item)
        $facebookInsights = $monitoringData['/insights'];
//        print_r($facebookInsights);
        $metricDate = $facebookInsights['data'][0]['values'][0]['end_time'];
        $metricDateObject = new DateTime($metricDate);
        $metricDateFormat = $metricDateObject->format('Y-m-d');
        foreach ($facebookInsights['data'] as $metric) {
            $metricName = $metric['name'];  // <== metric name
            $metricValue = $metric['values'][0]['value']; // <== metric value
            if ($metric['period'] == 'week') {
                $metricName .= '_week';
            }
            if ($metric['period'] == 'month') {
                $metricName .= '_month';
            }
            $newInsightsArray[$metricName] = $metricValue;
        }
        unset($newMonitoringData['/insights']);

        $newMonitoringData['/insights'] = $newInsightsArray;
        $newMonitoringData['date'] = $metricDateFormat;

//        print_r($monitoringData);
        
        return $newMonitoringData;
    }
} //End class
