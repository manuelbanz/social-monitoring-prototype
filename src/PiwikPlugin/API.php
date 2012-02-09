<?php
/**
 * Class Calculated
 *
 * This class delivers the statistics data from the database,
 * which will be displayed in piwik. Therefore datatables
 * are created based on a metric and a time range.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher_Source
 *
 */

class Piwik_SocialMonitoringTool_API
{
    /**
     * holds the instance of the api (this) class
     * @var null|Piwik_SocialMonitoring_API
     */
    static private $_instance = null;

    /**
     * holds the current date which was selected by the user in the frontend
     * @var string
     */
    private $_date;

    /**
     * holds the current date which was selected by the user in the frontend
     * eg. day, week, month
     * @var string
     */
    private $_period;

    /**
     * holds the current event array which will be used to display the event
     * graphs
     * @var array
     */
    private $eventArray;

    /**
     * the config file where each graph is defined
     * @var Zend_Config
     */
    static private $_config = null;

    /**
     * return the current instance of the api (this) class
     * @static
     * @return null|Piwik_SocialMonitoring_API
     */
    static public function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * singelton
     * returns the config file if exists or creates a new zend config
     * @static
     * @return null|Zend_Config
     */
    static public function getConfig()
    {
        if (self::$_config == null) {
            self::$_config = new Zend_Config(require 'piwik_config.php');
        }

        return self::$_config;
    }

    /**
     * checks if the current metric is a feedgroup (for tag clouds)
     * @param $metric
     * @return bool
     */
    protected function isFeedGroup($metric)
    {
        $select = "SELECT FeedGroup.id
                   FROM FeedGroup
                   WHERE FeedGroup.name = '$metric'";

        $result = Piwik_FetchRow($select);
        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *  get the metric type for a specific metric
     * (eg. value, multivalue)
     * @param  $metric
     * @return string
     */
    public function getMetricTypeFromDb($metric)
    {
        if ($this->isFeedGroup($metric)) {
            return 'cloud';
        } else {
            $select = "SELECT MetricGroup.type
                   FROM MetricGroup
                   WHERE MetricGroup.metricGroupKey = '$metric'";

            $result = Piwik_FetchRow($select);
            return $result['type'];
        }
    }


    /**
     * checks if 'metric' or 'metric1,metric2,..' is given
     * @param $metric
     * @return bool
     */
    protected function isOneMetric($metric)
    {
        $metricArray = explode(',', $metric);
        if (count($metricArray) == 1) {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * checks if '2011-01-01' or '2011-01-01,2011-01-01' is given
     * @param $date
     * @return bool
     */
    protected function isDateRange($date)
    {
        return (count(explode(',', $date)) > 1) ? true : false;
    }

    protected function setDateSelection($date)
    {
        if ($date == 'yesterday') {
            $date = date('Y-m-d', strtotime('yesterday'));
        }

        $this->_date = $date;
    }

    protected function setPeriodSelection($period)
    {
        $this->_period = $period;
    }

    public function getDateSelection()
    {
        return $this->_date;
    }

    public function getPeriodSelection()
    {
        return $this->_period;
    }

    /*
     * iterates the parameter array and changes the date format for the x axis.
     */
    protected function setXAxisDateFormat($array)
    {
        //change the date format for the x-axis
        foreach ($array as $date => $data)
        {
            if ($this->getPeriodSelection() == 'year') {
                $newDateArray[date('M', strtotime($date))] = $data;
            }
            else
            {
                $newDateArray[date('d.m.', strtotime($date))] = $data;
            }
        }
        return $newDateArray;
    }

    /**
     * gets the data for each graph from db and creates a piwik datatable
     * @param $date
     * @param $period
     * @param $metric
     * if $createDataTable is true a piwik datatable will be created,
     * else the data will be returned as an array
     * @param bool $createDataTable
     * @param string $folder
     * @return array|bool|Piwik_DataTable
     */
    function getDatatableForMetric(
        $date,
        $period,
        $metric,
        $createDataTable = true,
        $folder = null
    )
    {
        $this->setDateSelection($date);
        $this->setPeriodSelection($period);

        $outputArray = array();
        $metricType = null;

        if ($this->isOneMetric($metric)) {
            $metricType = $this->getMetricTypeFromDb($metric);
        } else {
            //OVERLAY GRAPHS
            $metrics = explode(',', $metric);
            $outputArray = $this->getOverlayData($metrics);
        }

        if ($metricType == 'cloud') {
            $outputArray = $this->getTagCloud($metric);
        }

        if ($metricType == 'value') {
            $outputArray = $this->getValueMetricData($metric);
            $outputArray = $this->setXAxisDateFormat($outputArray);
        }

        if ($metricType == 'multivalue') {
            //MULTIVALUE GRAPHS
            $outputArray = $this->getMultiValueData($metric);
        }

        $dataTable = null;
        if (!empty($outputArray)) {
            if ($createDataTable) {
                $dataTable = new Piwik_DataTable();
                $dataTable->addRowsFromArrayWithIndexLabel($outputArray);
            }
            else {
                //dont create a datatable, just return the array
                $dataTable = $outputArray;
            }
        }
        else
        {
            $dataTable = new Piwik_DataTable();
        }

        /*if ($metric == 'clicks' OR $metric == 'followers') {
            print_r($dataTable);
            echo "<br /> \n";
            die();
        }*/


        return $dataTable;
    }

    /**
     * selects the tags for a specific feedgroup from db
     * @param $feedGroup
     * @return array
     */
    protected function getTagCloud($feedGroup)
    {
        $select = "SELECT FeedGroupClustering.phrase, FeedGroupClustering.score
                   FROM FeedGroupClustering, FeedGroup
                   WHERE FeedGroup.name = '$feedGroup'
                   AND FeedGroup.id = FeedGroupClustering.feedGroupId";

        $dbResult = Piwik_FetchAll($select);

        $resultArray = array();
        foreach ($dbResult as $tag)
        {
            $resultArray[$tag['phrase']] = $tag['score'];
        }

        return $resultArray;
    }

    /**
     * creates an array for one event with correct x axis dates and returns it.
     * is called from getOverlayData for each event.
     * @param $eventName
     * @return array
     */
    protected function createEventArray($eventName)
    {
        $piwikDateRange = $this->createPiwikDateRange();

        $eventDate = $this->getEventDate($eventName);
        $eventStartDate = $eventDate[0];
        $eventEndDate = $eventDate[1];

        $eventYPos = 1;
        $eventArray = array();
        if ($eventEndDate == null) {
            $eventArray[$eventStartDate] = $eventYPos;
        }
        else
        {
            $eventArray[$eventStartDate] = $eventYPos;
            $eventArray[$eventEndDate] = $eventYPos;
        }

        $eventArray = $this->calculateXAxis(
            $eventArray,
            $piwikDateRange
        );

        return $eventArray;
    }

    /**
     * generates the event array and the datatables which will be
     * displayed in piwik as points and lines.
     * the method will be called from the controller event methods.
     * @return Piwik_DataTable
     */
    public function getEventDatatables()
    {
        //events array has not yet been created, so create it
        if (!is_array($this->eventArray)) {
            $piwikDateRange = $this->createPiwikDateRange();
            $dateStart = $piwikDateRange->getDateStart();

            $query = "SELECT Event.label
                  FROM Event
                  WHERE Event.startDate >= ?";

            $dbResult = Piwik_FetchAll($query, array($dateStart));

            $events = '';
            foreach ($dbResult as $event)
            {
                $events .= $event['label'] . ',';
            }
            $events = substr($events, 0, -1);
            $events = explode(',', $events);
            $eventArray = $this->getOverlayData($events);
            $this->eventArray = $eventArray;
        }

        $eventDatatables = new Piwik_DataTable();
        $eventDatatables->addRowsFromArrayWithIndexLabel($this->eventArray);

        return $eventDatatables;
    }

    /**
     * creates the data table for overlayed graphs
     * (more than one graph in one diagram)
     * @param $metrics
     * @return array
     */
    protected function getOverlayData($metrics)
    {
        $metricValueList = array();
        //get the data arrays for each metric and campaigns
        foreach ($metrics as $metricName)
        {
            $metricName = trim($metricName);
            $metricNameTranslated = $this->translateIfPossible($metricName);
            if ($this->isEvent($metricName)) {
                $metricValueList[$metricNameTranslated] =
                    $this->createEventArray($metricName);
            }
            else
            {
                $metricValueList[$metricNameTranslated] =
                    $this->getValueMetricData($metricName);
            }
        }

        $output = array();
        //for each metric get the timestamps and create a sorted array just with
        //the timestamps
        foreach ($metricValueList as $metric => $values)
        {
            $arrayKeys = array_keys($values);
            foreach ($arrayKeys as $date)
            {
                $temp[] = strtotime($date);
            }
            $output = array_unique(array_merge($output, $temp));
        }
        asort($output);
        $output = array_flip($output);

        //fill the date array with the metric data if data exists to this date
        foreach ($metricValueList as $metricName => $metricValueData)
        {
            foreach ($metricValueData as $key => $value)
            {
                $key = strtotime($key);
                if (array_key_exists($key, $output)) {
                    //$output[$key] = array();
                    if (is_array($output[$key])) {
                        $output[$key][$metricName] = $value;
                    }
                    else
                    {
                        $output[$key] = array($metricName => $value);
                    }
                }
            }
        }

        foreach ($metricValueList as $metricName => $metricValueData)
        {
            foreach ($output as $date => $values)
            {
                if (!array_key_exists($metricName, $values)) {
                    $values[$metricName] = 0;
                }
                $output[$date] = $values;
            }
        }

        $newOutput = array();
        foreach ($output as $key => $value)
        {
            arsort($value);
            if ($this->getPeriodSelection() == 'year') {
                $newOutput[date('M', $key)] = $value;
            }
            else
            {
                $newOutput[date('d.m.', $key)] = $value;
            }
        }

        return $newOutput;
    }

    /**
     * create the date range for which data will be displayed
     * eg. the last 30 days or the last 6 month
     * @return Piwik_Period_Range
     */
    protected function createPiwikDateRange()
    {
        $date = $this->getDateSelection();
        $period = $this->getPeriodSelection();

        if ($this->isDateRange($date)) {
            $dateRange = $date; //date looks like '2011-01-01,2011-02-02'
        }
        else
        {
            $timestamp = strtotime($date);
            switch ($period)
            {
                case 'day':
                    $startDate = date('Y-m-d', strtotime('-7 day', $timestamp));
                    break;
                case 'week':
                    $period = 'day';
                    $startDate = date('Y-m-d', strtotime('-7 day', $timestamp));
                    break;
                case 'month':
                    $period = 'day';
                    $startDate = date('Y-m-d', strtotime('-30 day', $timestamp));
                    break;
                case 'year':
                    $period = 'month';
                    $startDate = date('Y-m-d', strtotime('-6 month', $timestamp));
                    break;
            }

            //end date is the selected date
            $dateRange = $startDate . ',' . $date;
        }

        return new Piwik_Period_Range($period, $dateRange);
    }

    /**
     * check if a metric is campaign
     * @param $name
     * @return bool
     */
    protected function isEvent($name)
    {
        $query = "SELECT Event.id
                  FROM Event
                  WHERE Event.label = '$name'";
        $dbResult = Piwik_FetchAll($query);

        return (empty($dbResult)) ? false : true;
    }

    /**
     * get the start and end date for the given event
     * @param $event
     * @return array
     */
    protected
    function getEventDate($event)
    {
        $query = "SELECT Event.startDate, Event.endDate
                  FROM Event
                  WHERE Event.label = '$event'";
        $dbResult = Piwik_FetchRow($query);

        $eventDates[] = $dbResult['startDate'];
        $eventDates[] = $dbResult['endDate'];

        return $eventDates;
    }

    /**
     * calculate the trend value for the given metric
     * @param $metric
     * @return null|string
     */
    function getTrendValue($metric)
    {
        $date = $_REQUEST['date'];
        $period = $_REQUEST['period'];

        //get the data for the specific metric
        $dataTable = $this->getDatatableForMetric(
            $date,
            $period,
            $metric,
            false
        );

        //delete all NOVALUE data
        if ($dataTable != null) {
            do
            {
                $keyOfNoValue = array_search('NOVALUE', $dataTable, true);
                unset($dataTable[$keyOfNoValue]);
            } while ($keyOfNoValue == true);

            $count = count($dataTable);
            $countHalf = $count / 2;

            //split the data into two parts (if $count is odd the parts
            // will be extended by one element so they have the same length)
            if ($count & 1) {
                $countHalf++;
            }
            $firstHalf = array_slice($dataTable, 0, $countHalf);
            if ($count & 1) {
                $countHalf--;
            }
            $secondHalf = array_slice($dataTable, $countHalf);

            //calculate the average value for each part
            $countFirstHalf = count($firstHalf);
            $countSecondHalf = count($secondHalf);

            if ($countFirstHalf == 0) {
                $sumFirstHalf = 0;
            }
            else
            {
                $sumFirstHalf = array_sum($firstHalf) / $countFirstHalf;
            }

            if ($countSecondHalf == 0) {
                $sumSecondHalf = 0;
            }
            else
            {
                $sumSecondHalf = array_sum($secondHalf) / $countSecondHalf;
            }

            //calculate the percentage increase or decrease between the two average
            //values
            if ($sumFirstHalf == 0) {
                $percentValue = 0;
            }
            else
            {
                $percentValue = ($sumSecondHalf / $sumFirstHalf) * 100;
                $percentValue -= 100;
            }

            $percentValue = round($percentValue);
            //add + for increase and - for decrease
            $percentValueString = ($percentValue > 0)
                ? '+' . $percentValue
                : '' . $percentValue;
            return $percentValueString . '%';
        }
        else
        {
            return null;
        }
    }

    protected function isMailchimpMetric($metric)
    {
        $select = "SELECT MetricGroup.id
                   FROM MetricGroup, SourceType
                   WHERE MetricGroup.metricGroupKey = '$metric'
                   AND MetricGroup.sourceTypeId = SourceType.id
                   AND SourceType.key = 'Mailchimp'
                   ";
        $dbResult = Piwik_FetchAll($select);

        if (empty($dbResult)) {
            return false;
        }

        return true;
    }


    /**
     * get the data for a regular value metric form db and returns it as array
     * @param $metric
     * @param $folder
     * @return array
     */
    protected function getValueMetricData($metric, $folder = null)
    {
        $piwikDateRange = $this->createPiwikDateRange();
        $dateStart = $piwikDateRange->getDateStart();
        $dateEnd = $piwikDateRange->getDateEnd();

        if ($this->isMailchimpMetric($metric)) {
            //            $newsletterLabel = $this->getLastGerNewsletter();
            //todo SQL query liest mehrere Metriken aus, wenn mehrere Newsletter vorhanden sind!
            $query = "SELECT Data.value, Data.timestamp
                  FROM Data, Metric, Source, SourceType, Authentication
                  WHERE Data.metricId = Metric.id
                      AND Metric.metricKey = '$metric'
                      AND Data.sourceId = Source.id
                      AND Data.timestamp >= ?
                      AND Data.timestamp <= ?
                      AND Source.label = 'Sportalm Newsletter No2'
                  GROUP BY timestamp";
        }
        /*if ($folder != null AND $folder != 'null') {
            //not needed anymore:
           $select = "SELECT Source.lastDate
                     FROM Source, Authentication
                     WHERE Source.authId = Authentication.id
                     AND Authentication.objectId = '$folder'";
          $dbResult = Piwik_FetchAll($select);

          $lastDate = $dbResult[0]['lastDate'];

          $dateStart = $lastDate;
            $query = "SELECT Data.value, Data.timestamp
                  FROM Data, Metric, Source, SourceType, Authentication
                  WHERE Data.metricId = Metric.id
                      AND Data.timestamp >= ?
                      AND Data.timestamp <= ?
                      AND Metric.metricKey = '$metric'
                      AND Source.authId = Authentication.id
                      AND Authentication.objectId = '$folder'
                      AND Data.sourceId = Source.id
                      GROUP BY timestamp";
        }*/
        else
        {
            $query = "SELECT Data.value, Data.timestamp
                  FROM Data, Metric, Source, SourceType
                  WHERE Data.metricId = Metric.id
                      AND Data.timestamp >= ?
                      AND Data.timestamp <= ?
                      AND Metric.metricKey = '$metric'
                      AND Data.sourceId = Source.id
                      GROUP BY timestamp";
        }
        $dbResult = Piwik_FetchAll($query, array($dateStart, $dateEnd));
        $valueMetricData = array();
        foreach ($dbResult as $resultItem)
        {
            $valueDate = $resultItem['timestamp'];
            $valueMetricData[$valueDate] = $resultItem['value'];
        }

        //for each period (week, month..) the x axis shows days now
        //create xaxis based on the period:

        $valueMetricData = $this->calculateXAxis(
            $valueMetricData,
            $piwikDateRange
        );

        return $valueMetricData;
    }

    /**
     * creates the outputarray with correct dates on the x axis
     * which will be displayed in a diagram
     * @param $inputArray
     * @param $periodRange
     * @return array
     */
    protected function calculateXAxis($inputArray, $periodRange)
    {
        foreach ($periodRange->getSubperiods() as $subPeriod)
        {
            $subPeriods = explode(',', $subPeriod);
            //sub periods are all days of the period
            $tempArr = array();
            $dataEnd = false;
            foreach ($subPeriods as $subPeriodDay)
            {
                if (isset($inputArray[$subPeriodDay])) {
                    $tempArr[] = $inputArray[$subPeriodDay];
                }
                else
                {
                    $dataEnd = true;
                }
            }

            $xAxisValue = $subPeriod->toString();
            if (is_array($xAxisValue)) {
                $xAxisValue = $xAxisValue[0];
            }
            //calculate average of the subperiod
            if ($dataEnd AND count($tempArr) == 0) {
                $outputArray[$xAxisValue] = 'NOVALUE';
            }
            else
            {
                $value = array_sum($tempArr) / count($tempArr);
                $outputArray[$xAxisValue] =
                    round($value);
            }
        }

        return $outputArray;
    }

    /**
     * calculate difference between two dates in days
     * @param  $date1
     * @param  $date2
     * @return int
     */
    public function date_diff($date1, $date2)
    {
        $current = $date1;
        $datetime2 = date_create($date2);
        $count = 0;
        while (date_create($current) < $datetime2)
        {
            $current = gmdate(
                "Y-m-d",
                strtotime("+1 day", strtotime($current))
            );
            $count++;
        }
        return $count;
    }

    /**
     * get the data for multi value metrics and return it as array
     * @param $metric
     * @return array
     */
    protected function getMultiValueData($metric)
    {
        $date = $this->getDateSelection();
        if ($this->isDateRange($date)) {
            $date = explode(',', $date);
            //take the end date of the period
            $date = $date[1];
        }

        $newestDate = $this->getLastDateForMetric($metric);

        $query = "SELECT Metric.metricKey, Data.value, Data.timestamp
                  FROM Data, Metric, MetricGroup
                  WHERE Data.metricId = Metric.id
                      AND Data.timestamp = '$newestDate'
                      AND Metric.metricGroupId = MetricGroup.id
                      AND MetricGroup.metricGroupKey = '$metric'";

        if ($metric == 'page_views_external_referrals' OR
            $metric == 'page_views_internal_referrals'
        ) {
            $query = "SELECT Metric.metricKey, Data.value, Data.timestamp
                  FROM Data, Metric, MetricGroup
                  WHERE Data.metricId = Metric.id
                      AND Metric.metricGroupId = MetricGroup.id
                      AND MetricGroup.metricGroupKey = '$metric'";

            $dbResult = Piwik_FetchAll($query);

            $dbResultNew = array();
            foreach ($dbResult as $resultItem) {
                if (array_key_exists($resultItem['metricKey'], $dbResultNew)) {
                    $dbResultNew[$resultItem['metricKey']] += $resultItem['value'];
                } else {
                    $dbResultNew[$resultItem['metricKey']] = $resultItem['value'];
                }
            }

            asort($dbResultNew);

            $multiValueData = $dbResultNew;

        } else {
            $dbResult = Piwik_FetchAll($query);

            $multiValueData = array();
            $counter = 0;
            foreach ($dbResult as $resultItem)
            {
                if ($counter < 6) {
                    $xAxisKey = $resultItem['metricKey'];
                    $xAxisKey = $this->translateIfPossible($xAxisKey, $metric);
                    $multiValueData[$xAxisKey] = (int)$resultItem['value'];
                }
                $counter++;
            }
        }

        return $multiValueData;
    }

    /**
     * returns the date of the newest metric data
     * @param $metric
     * @return string date in format 'Y-m-d'
     */
    protected function getLastDateForMetric($metric)
    {
        $query = "SELECT Data.timestamp
                  FROM Data, Metric, MetricGroup
                  WHERE Data.metricId = Metric.id
                      AND Metric.metricGroupId = MetricGroup.id
                      AND MetricGroup.metricGroupKey = '$metric'
                  ORDER BY Data.timestamp DESC
                  LIMIT 1";

        $dbTimestamp = Piwik_FetchRow($query);
        return $dbTimestamp['timestamp'];
    }

    /**
     * translates a given string if it is defined in the config
     * @param  $key
     * @param null $metric
     * @return
     */
    protected function translateIfPossible($key, $metric = null)
    {
        if ($metric == null) {
            $metric = $key;
        }
        $config = $this->getConfig();
        $translations = $config->translations->toArray();
        if (isset($translations[$metric])) {
            $metricTranslation = $translations[$metric];
            if (isset($metricTranslation[$key])) {
                return $metricTranslation[$key];
            }
        }
        return $key;
    }

    /**
     * creates a new datetime object for the date string
     * and sets it 2 days in the past from now on
     * @param  $date
     * @return DateTime
     */
    protected function setDateTwoDaysInPast($date)
    {
        if ($date == 'yesterday') {
            $date = new DateTime();
            $date->modify('-2 day');
            $date = $date->format('Y-m-d');
        }
        else
        {
            $date = new DateTime($date);
            $currentDate = new DateTime();
            $date = $date->format('Y-m-d');
            $currentDate = $currentDate->format('Y-m-d');
            $diff = $this->date_diff($date, $currentDate);
            $date = new DateTime($date);
            if ($diff == 0) {
                $date->modify('-2 day');
            }
            if ($diff == 1) {
                $date->modify('-1 day');
            }
            $date = $date->format('Y-m-d');
        }
        return $date;
    }

    function getFriends($date, $period)
    {
        return $this->getDatatableForMetric(
            $date,
            $period,
            'friends'
        );
    }

    function getFollowers($date, $period)
    {
        return $this->getDatatableForMetric(
            $date,
            $period,
            'followers'
        );
    }


}