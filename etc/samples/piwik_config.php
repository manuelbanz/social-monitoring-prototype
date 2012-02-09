<?php

/**
 * This config will be loaded by the Piwik Plugin. For each array a widget will be created.
 */

return array(
    'widgets' => array(

        //TWITTER
        array(
            'widgetGroup' => 'Social Monitoring Widgets',
            'widgetName' => 'Twitter: Friends',
            'metric' => 'friends',
            'yAxis' => 'Friends',
            'type' => 'graphEvolution',
            'trend' => 'true'
        ),

        array(
            'widgetGroup' => 'Social Monitoring Widgets',
            'widgetName' => 'Twitter: Follower',
            'metric' => 'followers',
            'yAxis' => 'Followers',
            'type' => 'graphEvolution',
            'trend' => 'true'
        ),

     /*   array(
            'widgetGroup' => 'Social Monitoring Widgets',
            'widgetName' => 'Facebook: Likes pro Tag Insgesamt',
            'metric' => 'page_fan_adds',
            'yAxis' => 'Likes',
            'type' => 'graphEvolution',
            'trend' => 'true'
        ),

        array(
            'widgetGroup' => 'Social Monitoring Widgets',
            'widgetName' => 'Facebook: Likes pro Tag',
            'metric' => 'page_fan_adds_unique',
            'yAxis' => 'Likes',
            'type' => 'graphEvolution',
            'trend' => 'true'
        ),

        //FACEBOOK GRAPH PIEs:
        array(
            'widgetGroup' => 'Social Monitoring Widgets',
            'widgetName' => 'Facebook: Likes nach Geschlecht',
            'metric' => 'page_fans_gender',
            'yAxis' => 'Fans',
            'type' => 'graphPie'
        ),

        array(
            'widgetGroup' => 'Social Monitoring Widgets',
            'widgetName' => 'Facebook: Likes nach Sprache',
            'metric' => 'page_fans_locale',
            'yAxis' => 'Fans',
            'type' => 'graphPie'
        ),

        //OVERLAY GRAPHS:
        array(
            'widgetGroup' => 'Social Monitoring Widgets',
            'widgetName' => 'Facebook: Aktive User',
            'metric' =>
            'page_active_users,
                page_active_users_week,
                page_active_users_month',
            'yAxis' => 'Active Users',
            'type' => 'graphEvolution',
        ),*/
    ), // End widgets

    //TRANSLATION:
    //if one element of a metric is translated, only this element will be
    //displayed in the frontend
  /*  'translations' => array(
        'page_fans_gender' => array(
            'F' => 'Frauen',
            'M' => 'Männer',
            'U' => 'Ohne Angabe'
        ),

         'page_fans_locale' => array(
            'de_DE' => 'Deutsch',
            'en_US' => 'Englisch(US)',
            'en_GB' => 'Englisch(GB)',
            'it_IT' => 'Italien',
            'ru_RU' => 'Russland',
            'cs_CZ' => 'Tschechien',
            'bg_BG' => 'Bulgarien'
        ),

       'page_fans_city' => array(
            'vienna' => 'Wien',
            'munich' => 'München',
            'nuremberg' => 'Nürnberg',
            'frankfurt' => 'Frankfurt',
            'hamburg' => 'Hamburg',
            'dusseldorf' => 'Düsseldorf',
            'berlin' => 'Berlin',
            'hanover' => 'Hannover',
            'stuttgart' => 'Stuttgart'
        ),

        'page_fans_country' => array(
            'AT' => 'Österrreich',
            'DE' => 'Deutschland',
            'BG' => 'Bulgarien',
            'FR' => 'Frankreich',
            'CH' => 'Schweiz',
            'IT' => 'Italien',
            'GB' => 'England',
            'TR' => 'Türkei'
        ),
        
        'page_active_users' => array(
            'page_active_users' => 'pro Tag',
        ),

        'page_active_users_week' => array(
            'page_active_users_week' => 'pro Woche'
        ),

        'page_active_users_month' => array(
            'page_active_users_month' => 'pro Monat'
        )
    ) //End Translations*/
);