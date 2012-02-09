<?php
/**
 * Class SocialMonitoringTool_Controller
 *
 * Creates the widgets.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher_Source
 *
 */

class Piwik_SocialMonitoringTool_Controller extends Piwik_Controller
{

    protected function getMetricTypeFromDb($metric)
    {
        $api = Piwik_SocialMonitoring_API::getInstance();
        return $api->getMetricTypeFromDb($metric);
    }

    /**
     * function will be called by the controller functions for each graph
     * and creates the graphs in the frontend
     * @param $view
     * @param $metricType
     * @param bool $event
     * @return view
     */
    function drawGenericGraph($view, $metricType, $event = false)
    {
        if ($event) {
            $view->setHeight(70);
            $view->setXSteps(30);
            $view->disableFooter();
        } else {
            $view->setXSteps(1);
            if ($metricType != 'multivalue') {
                $view->disableFooter();
                if ($_REQUEST['period'] == 'range') {
                    $date = $_REQUEST['date'];
                    //manipulate teh xsteps for dateranges
                    if (count($dates = explode(',', $date)) > 1) {
                        $api = Piwik_SocialMonitoring_API::getInstance();
                        if ($api->date_diff($dates[0], $dates[1]) > 7) {
                            $view->setXSteps(3);
                        }
                    }
                } else {
                    if ($_REQUEST['period'] == 'month') $view->setXSteps(4);
                }
            }
        }
        return $this->renderView($view);
    }

    /**
     * Prints the trend value inside the widget.
     * @param $trendValue
     * @return string
     */
    function printTrendValue($trendValue) {
        echo "<h2 style='display: block; float: left;'>$trendValue</h2>";
        $path = 'plugins/SocialMonitoringTool/img/';
        if ($trendValue[1] == 0) {
            $imgUrl = $path . 'right.png';
        } else {
            $imgUrl = ($trendValue[0] == '+') ? $path .
                'up.png' : $path . 'down.png';
        }

        return "<img style='margin-top: 11px; width: 20px; height: 20px;' src='$imgUrl'>";
    }

    //the controller methods:

    function friends()
    {
        $api = Piwik_SocialMonitoring_API::getInstance();
        $trendValue = $api->getTrendValue('<?=$metric?>');
        echo $this->printTrendValue($trendValue);

        $view = Piwik_ViewDataTable::factory('graphEvolution');
        $view->init(
            $this->pluginName,
            __FUNCTION__,
            'SocialMonitoringTool.getFriends'
        );
        $view->setColumnTranslation('value', ' Friends');
        return $this->drawGenericGraph(
            $view,
            $this->getMetricTypeFromDb('friends')
        );
    }

    function followers()
    {
        $api = Piwik_SocialMonitoring_API::getInstance();
        $trendValue = $api->getTrendValue('<?=$metric?>');
        echo $this->printTrendValue($trendValue);

        $view = Piwik_ViewDataTable::factory('graphEvolution');
        $view->init(
            $this->pluginName,
            __FUNCTION__,
            'SocialMonitoringTool.getFollowers>'
        );
        $view->setColumnTranslation('value', ' Followers');
        return $this->drawGenericGraph(
            $view,
            $this->getMetricTypeFromDb('followers')
        );
    }
}