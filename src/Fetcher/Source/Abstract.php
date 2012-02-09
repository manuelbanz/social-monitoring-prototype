<?php
/**
 * Class Abstract
 *
 * Abstract source class. Every source extends from this class.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher_Source
 *
 */

abstract class Fetcher_Source_Abstract
{
    /**
     * The database id of this source from the database table 'Source'.
     * Needed in function saveMyMonitoringDataToDb() to connect the data
     * to the source.
     * @var integer
     */
    private $_dbId;

    /**
     * Holding the source type of the given source
     * eg. Facebook, Twitter etc.
     * @var string
     */
    private $_sourceType;

    /**
     * Is holding all monitoring data of this source.
     * @var App_Dto_MonitoringData[]
     */
    private $_monitoringDataList;

    /**
     * Array of different auth data.
     * @var Zend_Config
     */
    private $_authenticationData;

    /**
     * Fetches all new monitoring data from web of the specific source.
     * @abstract
     * @return array
     */
    public abstract function getNewMonitoringData();

    /**
     * create a zend config object and
     * set the all source information to this object
     * @param  $sourceInformation
     * @return void
     */
    protected function setSourceInformation($sourceInformation)
    {
        //set all source information and authentication data to this object:
        $this->setSourceType($sourceInformation['key']);
        //save authentication data
        $config = new Zend_Config(
            array(
                 'objectId' => $sourceInformation['objectId'],
                 'accessToken' => $sourceInformation['accessToken']
            )
        );
        $this->setAuthenticationData($config);
    }

    /**
     * get source information, like authentication data from db for given id
     * @return array Information which is needed to authenticate.
     */
    protected function getSourceInformationFromDb()
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $sourceId = $this->getDbId();
        //get source information from db:
        $where = "WHERE Authentication.id = Source.authId
                  AND SourceType.id = Source.sourceTypeId
                  AND Source.id = $sourceId";

        $select = 'SELECT SourceType.key, ' .
                  'Authentication.accessToken, Authentication.objectId ' .
                  'FROM Authentication, Source, SourceType ' . $where;
        $sourceInformation = $dbObject->fetchRow($select);
        return $sourceInformation;
    }

    /**
     * Save it all into database.
     *
     * @return void
     */
    public function saveMyMonitoringDataToDb()
    {
            //get DB object
            /** @var $db Zend_Db_Adapter_Abstract */
            $dbObject = Zend_Registry::get('DB');

        $skipped = false;
        /**
         * @var $monitoringData App_Dto_MonitoringData
         */
        foreach ($this->getMonitoringDataList() as $monitoringData) {
            $dbId = $this->getDbId();
            if ($this->alreadyInDb($monitoringData, $dbId)) {
                $skipped = true; //just needed for the output message
            } else {
                $insert = array(
                    'sourceId' => $dbId,
                    'metricId' => $monitoringData->getMetricId(),
                    'value' => $monitoringData->getValue(),
                    'timestamp' => $monitoringData->getTimestamp()
                );

                try {
                    $dbObject->insert('Data', $insert);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        if ($skipped) echo "skipped some data, due to duplicates\n";

        echo "\nsaved!\n";
    }

    /**
     * Check if entry in table Data already exists.
     * An entry consists of value + metricId + timestamp
     * @param  $monitoringData App_Dto_MonitoringData
     * @param $dbId
     * @return boolean
     */
    protected function alreadyInDb($monitoringData, $dbId)
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $value = $monitoringData->getValue();
        $metricId = $monitoringData->getMetricId();
        $timestamp = $monitoringData->getTimestamp();
        $select = "SELECT id
                   FROM Data
                   WHERE value = '$value'
                   AND sourceId = '$dbId'
                   AND metricId = $metricId
                   AND timestamp = '$timestamp'";

        $result = $dbObject->fetchRow($select);
        //kein ergebnis array vorhanden
        if (is_array($result)) {
            return true;
        }

        return false;
    }

    /**
     * Add the monitoring data from web
     * where a metric is found in the database to this object.
     *
     * For each MonitoringData Element a new App_Dto_MonitoringData object
     * is created and add to this object.
     * @param $monitoringData
     * @param string $date
     * @return void
     */
    public function setAllMonitoringData($monitoringData, $date = 'today')
    {
        foreach ($monitoringData as $apiCall => $apiCallData) {
            if (!is_array($apiCallData)) {
                $apiCallData = array($apiCall => $apiCallData);
            }

            //loop the incoming data
            foreach ($apiCallData as $metricKey => $dataElement) {
                $metricGroupId = $this->getMetricGroupIdFromDb($metricKey);
                //metric found in db -> has to be saved
                if (is_numeric($metricGroupId)) {
                    //multi value data:
                    if (is_array($dataElement)) {

                        if (array_key_exists(0, $dataElement)) {
                            $this->addPastValueMetricData(
                                $dataElement,
                                $metricKey,
                                $metricGroupId
                            );
                        } else {
                            $this->addMultiValueMetricData(
                                $dataElement,
                                $metricGroupId,
                                $date
                            );
                        }
                        //single value data:
                    } else {
                        $this->addValueMetricData(
                            $metricKey,
                            $dataElement,
                            $metricGroupId,
                            $date
                        );
                    }
                }
            }
        }
    }

    protected function addPastValueMetricData(
        $dataElement,
        $metrikKey,
        $metricGroupId
    )
    {
        foreach ($dataElement as $dayValue) {
            $this->addValueMetricData(
                $metrikKey,
                $dayValue['anzahl'],
                $metricGroupId,
                $dayValue['date']
            );
        }
    }


    /**
     * Add multi value data
     * eg. array (de_DE => 12, en_En => 21);
     * @param $dataElement array
     * @param $metricGroupId
     * @param $date
     * @return void
     */
    protected function addMultiValueMetricData(
        $dataElement,
        $metricGroupId,
        $date
    )
    {
        foreach ($dataElement as $subMetricKey => $subMetricData) {
            $subMetricId = $this->getMetricId($subMetricKey, $metricGroupId);
            //submetric not in db --> add it
            if ($subMetricId === null) {
                $this->addSubMetricToDb($subMetricKey, $metricGroupId);
                $subMetricId = $this->getMetricId($subMetricKey, $metricGroupId);
            }

            $monitoringData = new App_Dto_MonitoringData();
            $monitoringData->setMetric($subMetricKey);
            $monitoringData->setMetricId($subMetricId);
            $monitoringData->setMetricGroupId($metricGroupId);
            $monitoringData->setValue($subMetricData);
            if ($date == 'today') {
                $date = new DateTime();
                $date = $date->format('Y-m-d');
            }
            $monitoringData->setTimestamp($date);
            $this->addMonitoringData($monitoringData);
        }
    }

    /**
     * @param $subMetricKey
     * @param $metricGroupId
     * @return void
     */
    protected function addSubMetricToDb($subMetricKey, $metricGroupId)
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $insert = array(
            'metricKey' => $subMetricKey,
            'label' => 'Page Metric',
            'metricGroupId' => $metricGroupId
        );

        $dbObject->insert('Metric', $insert);
    }


    /**
     * Add one value
     * @param $metricKey
     * @param $dataElement
     * @param $metricGroupId
     * @param $date
     * @return void
     */
    protected function addValueMetricData(
        $metricKey,
        $dataElement,
        $metricGroupId,
        $date
    )
    {
        $monitoringData = new App_Dto_MonitoringData();
        $monitoringData->setMetric($metricKey);
        $metricId = $this->getMetricId($metricKey, $metricGroupId);
        $monitoringData->setMetricId($metricId);
        $monitoringData->setMetricGroupId($metricGroupId);
        $monitoringData->setValue($dataElement);
        if ($date == 'today') {
            $date = new DateTime();
            $date = $date->format('Y-m-d');
        }
        $monitoringData->setTimestamp($date);
        $this->addMonitoringData($monitoringData);
    }

    /**
     * returns the id of the specific metric group
     * @param $metricGroup
     * @return int
     */
    protected function getMetricGroupIdFromDb($metricGroup)
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        $select = "SELECT id
                   FROM MetricGroup
                   WHERE metricGroupKey = '$metricGroup'";

        $metricGroupIdRow = $dbObject->fetchRow($select);
        return $metricGroupIdRow['id'];
    }

    /**
     * returns the id for the specific metric from table metric
     * @param $metricKey
     * @param $metricGroupId
     * @return null|int
     */
    protected function getMetricId($metricKey, $metricGroupId)
    {
        //get DB object
        // @var $dbObject Zend_Db_Adapter_Abstract
        $dbObject = Zend_Registry::get('DB');

        $select = "SELECT Metric.id
                   FROM Metric, MetricGroup
                   WHERE metricKey = '$metricKey'
                   AND MetricGroup.id = '$metricGroupId'
                   AND Metric.metricGroupId = MetricGroup.id";

        $result = $dbObject->fetchRow($select);
        if (!empty($result)) {
            return $result['id'];
        } else {
            return null;
        }
    }

    public function getSourceType()
    {
        return $this->_sourceType;
    }

    public function setSourceType($sourceType)
    {
        $this->_sourceType = $sourceType;
    }

    /**
     * @return Zend_Config
     */
    public function getAuthenticationData()
    {
        return $this->_authenticationData;
    }

    public function setAuthenticationData($authenticationData)
    {
        $this->_authenticationData = $authenticationData;
    }

    public function setDbId($dbId)
    {
        $this->_dbId = $dbId;
    }

    public function getDbId()
    {
        return $this->_dbId;
    }

    /**
     * @param \App_Dto_MonitoringData $monitoringData
     * @return void
     */
    public function addMonitoringData($monitoringData)
    {
        $this->_monitoringDataList[] = $monitoringData;
    }

    /**
     * @return \App_Dto_MonitoringData
     */
    public function getMonitoringDataList()
    {
        return $this->_monitoringDataList;
    }
}
