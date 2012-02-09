<?php
/**
 * Class MonitoringFetcher
 *
 * Reads the sources from db and creates classes for that sources.
 *
 * @author      Manuel Banz
 * @date        2012
 * @package     Fetcher
 *
 */

class Fetcher_SourceFetcher
{
    /**
     * Get available sources + authentication data from db and
     * create Source objects for each source.
     * @return App_Source_SourceAbstract[]
     */
    public function getSources()
    {
        //get DB object
        /** @var $dbObject Zend_Db_Adapter_Abstract */
        $dbObject = Zend_Registry::get('DB');

        //get sources from db
        $select = 'SELECT Source.id, SourceType.key, Source.active
                   FROM Source, SourceType
                   WHERE Source.sourceTypeId = SourceType.id
                   ORDER BY Source.id';

        $dbSources = $dbObject->fetchAll($select);
        //create dto sourcelist
        /** @var App_Source_SourceAbstract[] */
        $sourceList = array();
        echo "create source list: \n";
        foreach ($dbSources as $dbSource) {
            $dbSourceId = $dbSource['id'];
            $dbSourceType = $dbSource['key'];
            $dbSourceActive = $dbSource['active'];
            //source will only be fetched if it is set active in db
            if ($dbSourceActive) {
                echo "   *  " . $dbSourceId . " - " . $dbSourceType . "\n";
                $newSource =
                        Fetcher_SourceFactory::create(
                            $dbSourceId,
                            $dbSourceType
                        );
            } else $newSource = null;

            if ($newSource instanceof Fetcher_Source_Abstract) {
                $sourceList[] = $newSource;
            }
        }
        return $sourceList;
    }
}
