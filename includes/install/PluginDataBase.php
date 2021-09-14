<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Install
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\install;

use g7mzr\webtemplate\application\plugins\InitiatePlugins;

/**
 * Description of PluginDataBase
 *
 * @author sandy
 */
class PluginDataBase
{
    /**
     * getPluginDBFiles
     *
     * This function is used to get the database schema and data files from the
     * active plugins
     *
     * @param string $pluginDir A string containing the base plugin directory.
     *
     * @return array containing the list of plugins and database files
     *
     * @access public
     */
    public static function getPluginDBFiles(string $pluginDir)
    {
        $app = new \g7mzr\webtemplate\application\Application();
        $pluginlist = InitiatePlugins::initatePlugins($pluginDir, $app);
        $filelist = array();
        foreach ($pluginlist as $pluginName => $pointer) {
            $filelist[$pluginName] = array();
            $filelist[$pluginName]['schema'] = $pointer->getDBSchema();
            $filelist[$pluginName]['data'] = $pointer->getDBData();
        }
        return $filelist;
    }



    /**
     * This function creates or updates the the tables for the active plugins
     *
     * @param array $installConfig Array with info needed to setup the app.
     * @param array $filenames     Array containing the filenames for the plugins.
     *
     * @return mixed
     */
    public static function createPluginSchema(array $installConfig, array $filenames)
    {
        $dsn = array(
            'dbtype'  => $installConfig['database_type'],
            'hostspec' => $installConfig['database_host'],
            'databasename' => $installConfig['database_name'],
            'username' => $installConfig['database_user'],
            'password' => $installConfig['database_user_passwd'],
            'disable_iso_date' => 'disable'
        );

        try {
            $dbmanager = new \g7mzr\db\DBManager(
                $dsn,
                $installConfig['database_superuser'],
                $installConfig['database_superuser_passwd']
            );
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return exit(1);
        }

        // CREATE/UPDATE the PLUGINS SCHEMA

        // Create or update the schema
        $setschemaresult = $dbmanager->setMode("schema");
        if (\g7mzr\db\common\Common::isError($setschemaresult)) {
            echo "Unable to switch DBManager to schema mode\n";
            return exit(1);
        }

        try {
            $schemaManager = new \g7mzr\db\SchemaManager($dbmanager);
        } catch (throwable $e) {
            echo "Unable to create new SchemaManager\n";
            echo $e->getMessage() . "\n";
            return exit(1);
        }

        echo "Creating Plugin Schemas:\n";
        foreach ($filenames as $pluginname => $localfilenames) {
            if ($localfilenames['schema'] == "") {
                continue;
            }


            $newinstall = false;

            $loadresult = $schemaManager->loadNewSchema($localfilenames['schema']);
            if (\g7mzr\db\common\Common::isError($loadresult)) {
                echo "Unable to load new Schema\n";
                echo $loadresult->getMessage() . "\n";
                return exit(1);
            }
            // Load the existing schema from the database
            $gotexistingschema = $schemaManager->getSchema();
            if (\g7mzr\db\common\Common::isError($gotexistingschema)) {
                if ($gotexistingschema->getCode() == DB_ERROR_NOT_FOUND) {
                    $newinstall = true;
                } else {
                    echo "Unable to load existing Schema\n";
                    return exit(1);
                }
            }

            if ($newinstall === true) {
                echo sprintf("Creating schema for %s \n", $pluginname);
                PluginDataBase::createTables($schemaManager, $localfilenames['schema']);
                echo "Done\n";
            } else {
                echo sprintf("Updating schema for %s \n", $pluginname);
                PluginDataBase::updateTables($schemaManager, $localfilenames['schema']);
                echo "Done\n";
            }
        }
    }


    /**
     * createTables
     *
     * This function creates the tables for the plugin
     *
     * @param \g7mzr\db\SchemaManager $schemaManager Database class object.
     *
     * @return boolean true
     *
     * @access private
     */
    private function createTables(\g7mzr\db\SchemaManager $schemaManager)
    {

        $result = $schemaManager->processNewSchema();
        if (\g7mzr\db\common\Common::isError($result)) {
            echo "Unable to process new Schema" . $schemaManager->getNewSchemaName() . "\n";
            return exit(1);
        }
        $saveresult = $schemaManager->saveSchema();
        if (\g7mzr\db\common\Common::isError($saveresult)) {
            echo "Unable to save Schema" . $schemaManager->getNewSchemaName() . "\n";
            return exit(1);
        }
    }


    /**
     *
     * This function updates the tables for the plugin
     *
     * @param \g7mzr\db\SchemaManager $schemaManager Database class object.
     *
     * @return boolean true
     *
     * @access private
     */
    private function updateTables(\g7mzr\db\SchemaManager $schemaManager)
    {
        if ($schemaManager->schemaChanged() === true) {
            $result = $schemaManager->processSchemaUpdate();
            if (\g7mzr\db\common\Common::isError($result)) {
                echo "Unable to process Schema Update" . $schemaManager->getNewSchemaName() . "\n";
                return exit(1);
            }
            $saveresult = $schemaManager->saveSchema();
            if (\g7mzr\db\common\Common::isError($saveresult)) {
                echo "Unable to save Schema" . $schemaManager->getNewSchemaName() . "\n";
                return exit(1);
            }
        }
    }
}
