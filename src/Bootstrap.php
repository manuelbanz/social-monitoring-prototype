<?php
/**
 * Bootstrap Class
 *
 */

class Bootstrap
{

    const LOG_TYPE_HTTPD = 'httpd';
    const LOG_TYPE_CLI = 'cli';

    public static function init()
    {
        define('SRC_PATH', realpath(dirname(__FILE__)));
        define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

        $origIncludePath = get_include_path();

        set_include_path(
            SRC_PATH . '/Lib' . PATH_SEPARATOR .
                SRC_PATH . '/Fetcher'
        );

        // setup autoloader:
        spl_autoload_register(array('Bootstrap', 'autoLoad'));

        // load basic config and put it in registry

        $config = new Zend_Config(require SRC_PATH . '/../etc/config.php');
        Zend_Registry::set('CONFIG', $config);

        // setup locale:

        setlocale(LC_ALL, $config->locale->default->lc_all);

        date_default_timezone_set(
            $config->locale->default->timezone
        );

        // setup database connection and put it in registry

        $db = Zend_Db::factory($config->database->adapter,
            $config->database->params->toArray());

        Zend_Registry::set('DB', $db);

        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    /**
     * Quicker autoload implementation
     * @static
     * @param string $className
     * @return void
     */
    public static function autoLoad($className)
    {
        $pathParts = explode('_', $className);

        if (in_array($pathParts[0], array('Zend'))) {
            array_unshift($pathParts, 'Lib');
        }
        $classFile =
            SRC_PATH . DIRECTORY_SEPARATOR
                . implode(DIRECTORY_SEPARATOR, $pathParts)
                . '.php';

        if (!file_exists($classFile)) {
            return;
        }

        require_once $classFile;
    }
}
