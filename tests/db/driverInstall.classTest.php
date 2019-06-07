<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Include the test Database configuration values
require_once dirname(__FILE__) . '/../_data/database.php';

/**
 * Postgresql Database Driver Class Unit Tests for Database Manipulation
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class PGDriverDBInstallTest extends TestCase
{
    /**
     * Database driver Object
     *
     * @var webtemplate\db\DB_DRIVER_PGSQL Object
     */
    protected $object;

    /**
     * Database connection flag
     *
     * @var
     */
    protected $databaseconnection;

    /**
     * Test Table
     *
     * @var
     */
    protected $testTable;

    /**
     * Test Table Name
     *
     * @var
     */
    protected $testTableName;

    /**
     * Test Table index
     *
     * @var
     */
    protected $index;

    /**
     * Test Table index name
     *
     * @var
     */
    protected $indexName;

    /**
     * Test Table existing index name
     *
     * @var
     */
    protected $existingIndexName;

    /**
     * New Column
     *
     * @var
     */
    protected $newColumn;

    /**
     * New Column Name
     *
     * @var
     */
    protected $newColumnName;

    /**
     * Drop Column Name
     *
     * @var
     */
    protected $dropColumnName;

    /**
     * Constraint
     *
     * @var
     */
    protected $constraints;

    /**
     * Constraint Name
     *
     * @var
     */
    protected $constraintsName;
    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp(): void
    {
        global $testdsn;

        $GLOBALS["unittest"] = true;

        $this->databaseconnection = false;

        $this->object = \webtemplate\db\DB::load($testdsn);

        if (!\webtemplate\general\General::isError($this->object)) {
            $this->databaseconnection = true;
        }
        $this->testTableName = 'testtable';
        $this->testTable = array(
            'COLUMNS' => array(
                'user_id' => array(
                    'TYPE' => 'serial',
                    'PRIMARY' => '1'
                ),
                'user_name' => array(
                    'TYPE' => 'varchar(64)',
                    'NOTNULL' => '1',
                    'UNIQUE' => 1
                ),
                'user_passwd' => array(
                    'TYPE' => 'varchar(255)',
                    'NOTNULL' => '1'
                ),
                'user_realname' => array(
                    'TYPE' => 'varchar(64)'
                ),
                'user_email' => array(
                    'TYPE' => 'varchar(64)'
                ),
                'user_enabled' => array(
                    'TYPE' => 'char(1)',
                    'DEFAULT' => 'Y',
                    'NOTNULL' => '1'
                ),
                'user_disable_mail' => array(
                    'TYPE' => 'char(1)',
                    'DEFAULT' => 'N',
                    'NOTNULL' => '1'
                ),
                'last_seen_date' => array(
                    'TYPE' => 'DATETIME'
                ),
                'passwd_changed' => array(
                    'TYPE' => 'DATETIME'
                ),
                'last_failed_login' => array(
                    'TYPE' => 'DATETIME'
                ),
                'group_id' => array(
                    'TYPE' => 'bigint'
                )
            )
        );
        $this->index = array('COLUMN' => 'user_realname');
        $this->indexName = 'testtable_user_id_idx';
        $this->existingIndexName = 'tokens_user_id_idx';

        $this->newColumnName = 'newpasswd';
        $this->newColumn = array(
            'TYPE' => 'char(1)',
            'DEFAULT' => 'N',
            'NOTNULL' => '1'
        );
        $this->dropColumnName = 'last_failed_login';

        $this->constraints = array('table' => 'groups', 'column' => 'group_id');
        $this->constraintsName = "fk_";
        $this->constraintsName .= $this->testTableName;
        $this->constraintsName .= '_';
        $this->constraintsName .= $this->constraints['column'];
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown(): void
    {
        global $testdsn, $options;


        // Drop the test table

        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $testdsn["hostspec"],
            '5432',
            $testdsn["database"],
            $testdsn["username"],
            $testdsn["password"]
        );

        // Create the PDO object and Connect to the database
        try {
            $localconn = new \PDO($conStr);
        } catch (\Exception $e) {
            //print_r($e->getMessage());
            throw new \Exception('Unable to connect to the database');
        }
        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $localconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        $sql = "DROP TABLE IF EXISTS testtable CASCADE";

        $localconn->query($sql);
        $localconn = null;
        $this->object->disconnect();
    }


    /**
     * Test that the database users can be created
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDatabaseUsers()
    {
        global $testdsn;

        // Connect to the database for these tests
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
             // Create a new User without Database and Role Create privilages
            $userName = "phpunit";
            $passwd = "phpUnit7";
            $unitTestDB = false;

            // User Does not exist.
            $result = $db->createUser($userName, $passwd, $unitTestDB);
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertTrue($result);
            } else {
                $this->fail("Error Creating the user");
            }

            // User already exists.
            $result = $db->createUser($userName, $passwd, $unitTestDB);
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertTrue($result);
            } else {
                $this->fail(__FUNCTION__ . ": Error Creating the user");
            }

            // Delete a User that DOES exist
            $result = $db->dropUser($userName);
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertTrue($result);
            } else {
                $this->fail(__FUNCTION__ . ": Error dropping the user");
            }

            // Delete a User that DOES Not exist
            $result = $db->dropUser($userName);
            if (!\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Error dropping the user");
            } else {
                $this->assertStringContainsString(
                    $result->getMessage(),
                    "Error Dropping Database User"
                );
            }


            // Create a new User with Database and Role Create privilages
            $userName = "phpunit";
            $passwd = "phpUnit7";
            $unitTestDB = true;

            // User Does not exist.
            $result = $db->createUser($userName, $passwd, $unitTestDB);
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertTrue($result);
            } else {
                $this->fail(__FUNCTION__ . ": Error Creating the user");
            }

            // Delete a User that DOES exist
            $result = $db->dropUser($userName);
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertTrue($result);
            } else {
                $this->fail(__FUNCTION__ . " :Error dropping the user");
            }

            // Try an create a user with a faulty name
            $userName = "php unit";
            $passwd = "phpUnit7";
            $unitTestDB = false;

            // User Does not exist.
            $result = $db->createUser($userName, $passwd, $unitTestDB);
            if (\webtemplate\general\General::isError($result)) {
                $this->assertStringContainsString(
                    'Error Creating Database User',
                    $result->getMessage()
                );
            } else {
                $this->fail(__FUNCTION__ . ": Error craeted user with faulty name");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that databases can be created
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreateDatabase()
    {
        global $testdsn;

        // Connect to the database for these tests
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            // Set up the variable for the new database;
            $userName = "testuser";
            $databaseName = "phpunitdb";
            $unitTestDB = false;

             // Create a new database
            $result = $db->createDatabase($databaseName, $userName);
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertTrue($result);
            } else {
                $this->fail(__FUNCTION__ . ": Error Creating the database");
            }

            // Create a database when it already exists
            $result = $db->createDatabase($databaseName, $userName);
            if (\webtemplate\general\General::isError($result)) {
                $this->assertStringContainsString(
                    "Error Creating New Database",
                    $result->getMessage()
                );
            } else {
                $this->fail(__FUNCTION__ . ": Failed creating Db when one exists");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that databases exists
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateDatabase
     *
     * @return null
     */
    public function testDatabaseExists()
    {
        global $testdsn;

        // Connect to the database for these tests
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            // Set up the variable for the test database
            $databaseName = "phpunitdb";

            // Check if the test database exists
            $result = $db->databaseExists($databaseName);
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertTrue($result);
            } else {
                $this->fail(__FUNCTION__ . ": Error checking the database exists");
            }

            // Check if a non existant database exists
            $result = $db->databaseExists('dummydb');
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertFalse($result);
            } else {
                $this->fail(__FUNCTION__ . ": Error checking 'dummydb' exists");
            }

            // Force an error
            $result = $db->databaseExists("/'");
            if (\webtemplate\general\General::isError($result)) {
                $this->assertStringContainsString(
                    'Error checking the database exists',
                    $result->getMessage()
                );
            } else {
                $this->fail(__FUNCTION__ . ": Forced error failed");
            }
        } else {
            $this->fail("Error Connecting to the Database");
        }
    }

    /**
     * Test that databases can be dropped
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDropDatabase()
    {
        global $testdsn;

        // Connect to the database for these tests
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            // Set up the variable for the new database;
            $userName = "testuser";
            $databaseName = "phpunitdb";
            $unitTestDB = false;

            // DROP the test database;
            $result = $db->dropDatabase($databaseName);
            if (!\webtemplate\general\General::isError($result)) {
                $this->assertTrue($result);
            } else {
                $this->fail(__FUNCTION__ . ": Error Dropping the database");
            }

            // DROP a database that does not exist
            $result = $db->dropDatabase($databaseName);
            if (!\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Error Dropping non existant database");
            } else {
                $this->assertStringContainsString(
                    "Error Dropping Database",
                    $result->getMessage()
                );
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can create a table with no indexes
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreateTableNoindexes()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Test Table");
            } else {
                $this->assertTrue($result);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can create a table with no indexes
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreateDuplicateTable()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Test Table");
            } else {
                $this->assertTrue($result);
                $result = $this->object->createTable(
                    $this->testTableName,
                    $this->testTable
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        "Error Creating the Table",
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Created Duplicate Table");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can create a table with indexes
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testCreateTableWithindexes()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['INDEXES'] = array($this->indexName => $this->index);
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Test Table");
            } else {
                $this->assertTrue($result);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can create a table with indexes
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testCreateUniqueIndexes()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Test Table");
            } else {
                $this->assertTrue($result);
                $indexdata = array('COLUMN' => "user_email", "UNIQUE" => 1);
                $result = $this->object->createIndex(
                    $this->testTableName,
                    $this->testTableName . "_email_key",
                    $indexdata
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->fail(__FUNCTION__ . ": Failed to create index");
                } else {
                    $this->assertTrue($result);
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test we can drop an index
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testDropIndex()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Test Table");
            } else {
                $this->assertTrue($result);
                $indexdata = array('COLUMN' => "user_email", "UNIQUE" => 1);
                $result = $this->object->createIndex(
                    $this->testTableName,
                    $this->testTableName . "_email_key",
                    $indexdata
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->fail(__FUNCTION__ . ": Failed to create index");
                } else {
                    $this->assertTrue($result);
                    $result = $this->object->dropIndex(
                        $this->testTableName,
                        $this->testTableName . "_email_key"
                    );
                    if (\webtemplate\general\General::isError($result)) {
                        $this->fail(__FUNCTION__ . ": Failed to drop index");
                    } else {
                        $this->assertTrue($result);
                    }
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can drop an index
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testDropIndexError()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Test Table");
            } else {
                $this->assertTrue($result);
                $this->assertTrue($result);
                $result = $this->object->dropIndex(
                    $this->testTableName,
                    $this->testTableName . "_email key"
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        "Error Dropping Index",
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Dropped Fake Index");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test we fail to create a table with duplicate indexes
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testCreateTableDuplicateindexes()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['INDEXES'] = array(
                $this->indexName => $this->index,
                $this->existingIndexName => $this->index
            );
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->assertStringContainsString(
                    "Error Creating Indexes on Table " . $this->testTableName,
                    $result->getMessage()
                );
            } else {
                $this->Fail(__FUNCTION__ . ": Created Duplicate Index");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can drop a table
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testDropTable()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Test Table");
            } else {
                $this->assertTrue($result);
                $result = $this->object->dropTable($this->testTableName);
                if (\webtemplate\general\General::isError($result)) {
                    $this->fail(__FUNCTION__ . ": Failed to create Test Table");
                } else {
                    $this->assertTrue($result);
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can drop a table
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testDropTableError()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->dropTable("table name");
            if (\webtemplate\general\General::isError($result)) {
                $this->assertStringContainsString(
                    "Error Dropping Table",
                    $result->getMessage()
                );
            } else {
                $this->fail(__FUNCTION__ . ": Dropped Invalid Table");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test we can add a column to a table
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAddColumn()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);


                $result = $this->object->addColumn(
                    $this->testTableName,
                    $this->newColumnName,
                    $this->newColumn
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->fail(__FUNCTION__ . ": Failed to add column");
                } else {
                    $this->assertTrue($result);
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can add a column to a table
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAddColumnError()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);


                // Test adding a duplicate column to the table
                $result = $this->object->addColumn(
                    $this->testTableName,
                    'group_id',
                    $this->newColumn
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error adding column to testtable',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Added duplicate column");
                }

                // Test adding a column with a faulty FK
                $this->newColumn['CONSTRAINTS'] = array(
                    'table' => 'groups',
                    'column' => 'test'
                );
                $result = $this->object->addColumn(
                    $this->testTableName,
                    $this->newColumnName,
                    $this->newColumn
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error Creating the FK on Table testtable',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Added faulty FK to Column");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test we can add a column to a table
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testDropColumn()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);

                // Drop an existing column
                $result = $this->object->dropColumn(
                    $this->testTableName,
                    $this->dropColumnName
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->fail(__FUNCTION__ . ": Failed to drop column");
                } else {
                    $this->assertTrue($result);
                }

                // Drop an non existing column
                $result = $this->object->dropColumn(
                    $this->testTableName,
                    $this->dropColumnName
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error dropping column on testtable',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Failed dropped dummy column");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can add a constraint to a column
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAddConstraint()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['CONSTRAINTS']
                = $this->constraints;

            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we get an error if the wrong constraint is added
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAddConstraintError()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['CONSTRAINTS']
                = array('table' => 'groups', 'column' => 'test');

            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->assertStringContainsString(
                    "Error Creating the FK on Table testtable",
                    $result->getMessage()
                );
            } else {
                $this->fail(__FUNCTION__ . ": Created Table instead of error");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we can drop a constraint
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testDropConstraint()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['CONSTRAINTS']
                = $this->constraints;

            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $result = $this->object->dropFK(
                    $this->testTableName,
                    $this->constraintsName
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->fail(__FUNCTION__ . ": Failed to Drop Constraint");
                } else {
                    $this->assertTrue($result);
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test we cannot drop an invalid constraint
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testDropConstraintError()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['CONSTRAINTS']
                = $this->constraints;

            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $result = $this->object->dropFK(
                    $this->testTableName,
                    "fk_" . $this->testTableName . "_test"
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        "Error deleteting Foreign Key fk_testtable_test",
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Dropped Constraint");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }



    /**
     * Test error reporting on CreateUnique Contraint
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testCreateUniqueConstraintError()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $keydata = array("table", "column");
                $result = $this->object->createUniqueConstraint($keydata);
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        "Error Creating Unique Constraints.",
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Created Constraint");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that FKs can be dropped as part of Column change
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnDropFK()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['CONSTRAINTS']
                = $this->constraints;
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('OLDCONSTRAINTS' => true);
                $newcolumn = array('TYPE' => 'bigint');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (!\webtemplate\general\General::isError($result)) {
                    $this->assertTrue($result);
                } else {
                    $this->fail(__FUNCTION__ . ": Failed to drop FK");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test errors will be reported if a non existant FK is dropped as
     *  part of Column change
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnDropFKError()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('OLDCONSTRAINTS' => true);
                $newcolumn = array('TYPE' => 'bigint');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error dropping Foreign Key on',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Failed to drop FK");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }



    /**
     * Test that the Column type can be changed
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnChangeType()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('TYPE' => true);
                $newcolumn = array('TYPE' => 'integer');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (!\webtemplate\general\General::isError($result)) {
                    $this->assertTrue($result);
                } else {
                    $this->fail(__FUNCTION__ . ": Failed to Change Column Type");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that errors are reported when Column type changes fail
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnChangeTypeError()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('TYPE' => true);
                $newcolumn = array('TYPE' => 'ints');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error changing column Type',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Column Type Changed");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that NOT NULL can be set on the column
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnSetNotNULL()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('NOTNULL' => true);
                $newcolumn = array('TYPE' => 'bigint', 'NOTNULL' => '1');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (!\webtemplate\general\General::isError($result)) {
                    $this->assertTrue($result);
                } else {
                    $this->fail(__FUNCTION__ . ": Failed Set NOT NULL on Column");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that NOT NULL can be dropped from the column
     * NOTNULL = '0' in NEW COLUMN
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnDROPNotNULLInColDesc()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['NOTNULL'] = '1';
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('NOTNULL' => true);
                $newcolumn = array('TYPE' => 'bigint', 'NOTNULL' => '0');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (!\webtemplate\general\General::isError($result)) {
                    $this->assertTrue($result);
                } else {
                    $this->fail(__FUNCTION__ . ": Failed to DROP NOTNULL on Column");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that NOT NULL can be dropped from the column
     * NOTNULL = '0' not in NEW COLUMN
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnDROPNotNULLNotInColDesc()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['NOTNULL'] = '1';
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('NOTNULL' => true);
                $newcolumn = array('TYPE' => 'bigint');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (!\webtemplate\general\General::isError($result)) {
                    $this->assertTrue($result);
                } else {
                    $this->fail(__FUNCTION__ . ": Failed Set NOT NULL on Column");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that NOT NULL can raise an error
     * NOTNULL = '0' in NEW COLUMN
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnNOTNULLError()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['NOTNULL'] = '1';
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('NOTNULL' => true);
                $newcolumn = array('TYPE' => 'bigint');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_ids",
                    $newcolumn,
                    $changes
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error changing column notnull',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Failed NOT NULL Error Test");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that DEFAULT value can be set on the column
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnSetDEFAULT()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('DEFAULT' => true);
                $newcolumn = array('TYPE' => 'bigint', 'DEFAULT' => 1);
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (!\webtemplate\general\General::isError($result)) {
                    $this->assertTrue($result);
                } else {
                    $this->fail(__FUNCTION__ . ": Failed setting DEFAULT on Column");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that DEFAULT value can be set on the column
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnDropDEFAULT()
    {
        if ($this->databaseconnection == true) {
            $this->testTable['COLUMNS']['group_id']['DEFAULT'] = 1;
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('DEFAULT' => true);
                $newcolumn = array('TYPE' => 'bigint');
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (!\webtemplate\general\General::isError($result)) {
                    $this->assertTrue($result);
                } else {
                    $this->fail(__FUNCTION__ . ": Failed to drop DEFAULT on Column");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that DEFAULT value on a non existant column causes an error
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnSetDEFAULTError()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('DEFAULT' => true);
                $newcolumn = array('TYPE' => 'bigint', 'DEFAULT' => 1);
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_ids",
                    $newcolumn,
                    $changes
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error changing column DEFAULT',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Failed DEFAULT error testing");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that DEFAULT value can be set on the column
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnFK()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('CONSTRAINTS' => true);
                $newcolumn = array(
                    'TYPE' => 'bigint',
                    'CONSTRAINTS' => array(
                        'table' => 'groups',
                        'column' => 'group_id'
                    )
                );
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (!\webtemplate\general\General::isError($result)) {
                    $this->assertTrue($result);
                } else {
                    $this->fail(__FUNCTION__ . ": Failed to set column constraints");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that DEFAULT value can be set on the column
     *
     * @group unittest
     * @group database
     *
     * @depends testCreateTableNoindexes
     *
     * @return null
     */
    public function testAlterColumnFKError()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createTable(
                $this->testTableName,
                $this->testTable
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to create Table");
            } else {
                $this->assertTrue($result);
                $changes = array('CONSTRAINTS' => true);
                $newcolumn = array(
                    'TYPE' => 'bigint',
                    'CONSTRAINTS' => array(
                        'table' => 'groups',
                        'column' => 'group_ids'
                    )
                );
                $result = $this->object->alterColumn(
                    $this->testTableName,
                    "group_id",
                    $newcolumn,
                    $changes
                );
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error Creating the FK on Table',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Failed setting Error Constraints");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Get the Test Database Schema
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testGetSchema()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->getSchema();
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": Failed to get Schema");
            } else {
                $this->assertEquals("1.00", $result['version']);
                $this->assertTrue(array_key_exists('users', $result['schema']));
                $this->assertTrue(array_key_exists('admin', $result['groups']));
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Try and get the schema from an non existant table
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testGetSchemaNoTable()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->getSchema("dummy");
            if (\webtemplate\general\General::isError($result)) {
                $this->assertStringContainsString(
                    'SQL Query Error',
                    $result->getMessage()
                );
            } else {
                $this->fail(__FUNCTION__ . ": Got Schema from non existant table");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Try and get the schema from an empty table
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testGetSchemaEmptyTable()
    {
        if ($this->databaseconnection == true) {
            $tabledef['COLUMNS'] = array(
                'version' => array('TYPE' => 'numeric(3,2)', 'NOTNULL' => '1'),
                'schema' => array('TYPE' => 'text', 'NOTNULL' => '1'),
                'groups' => array('TYPE' => 'text', 'NOTNULL' => '1')
            );

            $result = $this->object->createtable($this->testTableName, $tabledef);
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": error Creating table");
            } else {
                $result = $this->object->getSchema($this->testTableName);
                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Schema Not Found',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Schema from non existant table");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Save the schema to the database using the default table
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testSaveSchema()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->saveSchema(
                \webtemplate\db\schema\SchemaData::$schema_version,
                \webtemplate\db\schema\SchemaData::$schema,
                \webtemplate\db\schema\SchemaData::$defaultgroups
            );

            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . "Failed to Save Schema");
            } else {
                $this->assertTrue($result);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Save the schema to the database using an non existant Table
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testSaveSchemaNoTable()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->saveSchema(
                \webtemplate\db\schema\SchemaData::$schema_version,
                \webtemplate\db\schema\SchemaData::$schema,
                \webtemplate\db\schema\SchemaData::$defaultgroups,
                "dummy"
            );

            if (\webtemplate\general\General::isError($result)) {
                $this->assertStringContainsString(
                    'Error Deleteting Previous Schema',
                    $result->getMessage()
                );
            } else {
                 $this->fail(__FUNCTION__ . "Save Schema to dummy table");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Save the schema to the database using a faulty Table.  One field is missing
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testSaveSchemaNFaultyTable()
    {
        if ($this->databaseconnection == true) {
            $tabledef['COLUMNS'] = array(
                'version' => array('TYPE' => 'numeric(3,2)', 'NOTNULL' => '1'),
                'schema' => array('TYPE' => 'text', 'NOTNULL' => '1')
            );

            $result = $this->object->createtable($this->testTableName, $tabledef);
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": error Creating table");
            } else {
                $result = $this->object->saveSchema(
                    \webtemplate\db\schema\SchemaData::$schema_version,
                    \webtemplate\db\schema\SchemaData::$schema,
                    \webtemplate\db\schema\SchemaData::$defaultgroups,
                    $this->testTableName
                );

                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error EXECUTING Schema INSERT',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Saved Schema to dummy table");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }



    /**
     * Save the schema to the database using faulty data
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testSaveSchemaNFaultyData()
    {
        if ($this->databaseconnection == true) {
            $tabledef['COLUMNS'] = array(
                'version' => array('TYPE' => 'numeric(3,2)', 'NOTNULL' => '1'),
                'schema' => array('TYPE' => 'text', 'NOTNULL' => '1'),
                'groups' => array('TYPE' => 'text', 'NOTNULL' => '1')
            );

            $result = $this->object->createtable($this->testTableName, $tabledef);
            if (\webtemplate\general\General::isError($result)) {
                $this->fail(__FUNCTION__ . ": error Creating table");
            } else {
                $result = $this->object->saveSchema(
                    'text',
                    \webtemplate\db\schema\SchemaData::$schema,
                    \webtemplate\db\schema\SchemaData::$defaultgroups,
                    $this->testTableName
                );

                if (\webtemplate\general\General::isError($result)) {
                    $this->assertStringContainsString(
                        'Error EXECUTING Schema INSERT',
                        $result->getMessage()
                    );
                } else {
                    $this->fail(__FUNCTION__ . ": Saved Schema to dummy table");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
}
