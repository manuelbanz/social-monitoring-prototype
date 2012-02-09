<?php
/**
 * Class Calculated
 *
 * This source calculates kpis from other metrics.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher_Source
 *
 */

class Fetcher_Source_Calculated extends Fetcher_Source_Abstract
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

    public function getNewMonitoringData()
    {
        $monitoringData['engagement'] = $this->getFacebookEngagementRate();
        $monitoringData['date'] =
            $this->getLastDateForMetric('page_active_users');
        return $monitoringData;
    }

    /**
     * Calculates the engagement rate (monthly active user per total fans)
     * for facebook.
     * @return int
     */
    protected function getFacebookEngagementRate()
    {
        $date = $this->getLastDateForMetric('page_active_users_month');
        $pageFansLifetime =
            $this->getMetricValueFromDb('page_fans', $date);
        $pageActiveUsersMonthly =
            $this->getMetricValueFromDb('page_active_users_month', $date);
        $lifetimeFans = $pageFansLifetime['value'];
        $activeUsers = $pageActiveUsersMonthly['value'];
        $engagementRate =
            round(($activeUsers / $lifetimeFans) * 100);
        return $engagementRate;
    }

     /**
     * returns the date of the newest metric data
     * @param $metric
     * @return string date in format 'Y-m-d'
     */
    protected function getLastDateForMetric($metric)
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $query = "SELECT Data.timestamp
                  FROM Data, Metric, MetricGroup
                  WHERE Data.metricId = Metric.id
                      AND Metric.metricGroupId = MetricGroup.id
                      AND MetricGroup.metricGroupKey = '$metric'
                  ORDER BY Data.timestamp DESC
                  LIMIT 1";

        $dbTimestamp = $dbObject->fetchAll($query);
        
        return $dbTimestamp[0]['timestamp'];
    }

    /**
     * Gives back a value for a given metric from the database.
     * @param  $metric string
     * @param  $date mixed
     * @return array
     */
    protected function getMetricValueFromDb($metric, $date)
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        if ($date instanceof DateTime) {
            $date = $date->format('Y-m-d');
        }

        $query = "SELECT Data.value
                  FROM Data, Metric, Source
                  WHERE Data.metricId = Metric.id
                      AND Data.timestamp = '$date'
                      AND Metric.metricKey = '$metric'
                      AND Data.sourceId = Source.id";

        $result = $dbObject->fetchRow($query);
        return $result;
    }
}
