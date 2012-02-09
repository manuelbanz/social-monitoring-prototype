<?php
/**
 * Class MonitoringData
 *
 * This is a data transfer object for one metric and its value.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher
 *
 */

class App_Dto_MonitoringData
{

    private $_value;

    /**
     * @var $_metric string
     */
    private $_metric;

    /**
     * @var $_metricId int
     */
    private $_metricId;

    /**
     * @var $_metricGroupId int
     */
    private $_metricGroupId;

    /**
     * @var $_timestamp string
     */
    private $_timestamp;

    public function setMetric($metric)
    {
        $this->_metric = $metric;
    }

    public function getMetric()
    {
        return $this->_metric;
    }

    public function setTimestamp($timestamp)
    {
        $this->_timestamp = $timestamp;
    }

    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setMetricId($metricId)
    {
        $this->_metricId = $metricId;
    }

    public function getMetricId()
    {
        return $this->_metricId;
    }

    public function setMetricGroupId($metricGroupId)
    {
        $this->_metricGroupId = $metricGroupId;
    }

    public function getMetricGroupId()
    {
        return $this->_metricGroupId;
    }
}
