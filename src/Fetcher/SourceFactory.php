<?php
/**
 * Class MonitoringFetcher
 *
 * Instanziates the source classes, if they exist.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher
 *
 */

class Fetcher_SourceFactory
{
    public static function create($sourceId, $sourceType)
    {
        $className = 'Fetcher_Source_'.$sourceType;
        //check if class exists
        if (class_exists($className)) {
            return new $className($sourceId);
        } else {
            throw new Exception('Unknown Source type: ' . $sourceType);
        }
    }
}
