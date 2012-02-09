<?php

/**
 *  This config file has to be updated. Change database information.
 */

return array(
	'httpd' => array(
		'protocol' => 'http',
		'host' => 'local.piwik',
		'path'	=> 'htdocs'
	),
    'database' => array(
        'adapter' => 'pdo_mysql',
        'params'  => array(
            'host'     => 'localhost',
            'username' => 'root',
            'password' => 'root',
            'dbname'   => 'piwk',
	        'driver_options'  => array(
	            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\''
			)	        
        )
    ),
    'locale' => array(
    	'default' => array(
    		'lc_all' 	=> 'C',
    		'timezone' 	=> 'Europe/Berlin',
    	)
    ),
);
