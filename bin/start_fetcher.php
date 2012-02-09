<?php
/**
 * Author: Manuel Banz
 * Date: 04.01.12
 *
 * Used to start the fetcher, by calling: php social_monitoring_tool/bin/start_fetcher.php
 */


//autoloading classes:
require dirname(__FILE__)."/../src/Bootstrap.php";
Bootstrap::init();

//start monitoring fetcher
$monitoringFetcher = new Fetcher_MonitoringFetcher();
$monitoringFetcher->run();


