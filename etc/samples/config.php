<?php

/**
 *  This config file has to be updated. Change database information.
 */

return array(
	'httpd' => array(
		'protocol' => 'http',
		'host' => 'bas-socmon.local.basilicom.de',
		'path'	=> 'htdocs'
	),
    'database' => array(
        'adapter' => 'pdo_mysql',
        'params'  => array(
            'host'     => 'localhost',
            'username' => 'root',
            'password' => 'root',
            'dbname'   => 'db',
	        'driver_options'  => array(
	            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\''
                // in case of trouble:
                // http://www.mysqltalk.com/blog/?p=9
                // ,PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
			)	        
        )
    ),
    'locale' => array(
    	'default' => array(
    		'lc_all' 	=> 'C',
    		'timezone' 	=> 'Europe/Berlin',
    	)
    )
);
