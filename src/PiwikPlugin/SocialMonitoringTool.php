<?php
/**
 * Class SocialMonitoringTool
 *
 * First plugin class.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher_Source
 *
 */


class Piwik_SocialMonitoringTool extends Piwik_Plugin
{

    /**
     * Return information about this plugin.
     *
     * @see Piwik_Plugin
     *
     * @return array
     */
    public function getInformation()
    {
        return array(
            'description' => 'Social Monitoring Tool',
            'author' => 'Manuel Banz',
            'author_homepage' => 'http://manuelbanz.de/',
            'version' => '0.1',
        );
    }

    function postLoad()
    {
        //get config from api
        $config = Piwik_SocialMonitoringTool_API::getConfig();
        $widgets = $config->widgets->toArray();

        foreach ($widgets as $widget) {
            Piwik_AddWidget(
                $widget['widgetGroup'],
                $widget['widgetName'],
                'SocialMonitoring',
                $widget['metric']
            );
        }
    }
}


