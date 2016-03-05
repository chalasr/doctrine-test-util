<?php

/*
 * This file is part of the RCH package.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this recorder code.
 */

namespace RCH\DoctrineTestUtil;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

/**
 * Provides a doctrine testing environment.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DatabaseConnection
{
    /** @var array */
    protected static $_config;

    /** @var EntityManager */
    protected static $_em;

    /**
     * Get database connection.
     *
     * @static
     *
     * @return DatabaseConnection
     */
    public static function create($mappingPath)
    {
        self::$_config = array(
            'dbname' => $GLOBALS['db_name'],
            'driver'   => 'pdo_mysql',
            'host' => $GLOBALS['db_host'],
            'user' => $GLOBALS['db_user'],
            'password' => $GLOBALS['db_password'],
            'charset' => 'UTF8',
        );


        self::createDatabase(self::$_config);

        /** @var \Doctrine\ORM\Configuration */
        $metadataConfiguration = Setup::createAnnotationMetadataConfiguration(array($mappingPath), true, null, null, false);
        self::$_em = EntityManager::create(self::$_config, $metadataConfiguration);

        $pdo = self::$_em->getConnection()->getWrappedConnection();
        self::$_em->clear();

        $tool = new SchemaTool(self::$_em);
        $classes = self::$_em->getMetaDataFactory()->getAllMetaData();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        return new self();
    }

    public static function createDatabase($path)
    {
        $path = array(
            'driver' => $GLOBALS['db_driver'],
            'host' => $GLOBALS['db_host'],
            'user' => $GLOBALS['db_user'],
            'password' => $GLOBALS['db_password'],
        );

        $tmpConnection = DriverManager::getConnection($path);

        if (in_array($GLOBALS['db_name'], $tmpConnection->getSchemaManager()->listDatabases())) {
            return;
        }

        $tmpConnection->getSchemaManager()->createDatabase($GLOBALS['db_name']);

        $tmpConnection->close();
    }

    /**
     * Get entity manager.
     *
     * @static
     *
     * @return EntityManager
     */
    public static function getEntityManager()
    {
        return self::$_em;
    }

    /**
     * Get PHPUnit database connection.
     *
     * @static
     *
     * @param EntityManager|null $entityManager
     *
     * @return \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    public static function getDatabaseConnectionForTest(EntityManager $entityManager = null)
    {
        if (!$entityManager instanceof EntityManager) {
            $entityManager = self::$_em;
        }

        $pdo = $entityManager->getConnection()->getWrappedConnection();

        return new \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($pdo, $GLOBALS['db_name']);
    }

    /**
     * Get PHPUnit database test case.
     *
     * @static
     *
     * @param \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection|null $connection
     *
     * @return \PHPUnit_Extensions_Database_DefaultTester
     */
    public static function getDatabaseTestCase(\PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection $connection = null)
    {
        if (!$connection) {
            $connection = self::getDatabaseConnectionForTest();
        }

        return new \PHPUnit_Extensions_Database_DefaultTester($connection);
    }
}
