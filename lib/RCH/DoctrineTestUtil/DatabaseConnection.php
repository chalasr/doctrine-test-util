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
    /**
     * Get database connection.
     *
     * @static
     *
     * @return DatabaseConnection
     */
    public static function create()
    {
        $entityManager = self::getEntityManager();
        $pdo = $entityManager->getConnection()->getWrappedConnection();
        $entityManager->clear();

        $tool = new SchemaTool($entityManager);
        $classes = $entityManager->getMetaDataFactory()->getAllMetaData();

        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        return new self();
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
        $mysqlConnection = array('url' => sprintf(
            'mysql://%s:%s@%s/%s', $GLOBALS['db_user'], $GLOBALS['db_password'], $GLOBALS['db_host'], $GLOBALS['db_name']
        ));

        /* @var \Doctrine\ORM\Configuration */
        $metadataConfiguration = Setup::createAnnotationMetadataConfiguration(array(__DIR__.'/Entity'), true, null, null, false);

        return EntityManager::create($mysqlConnection, $metadataConfiguration);
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
            $entityManager = self::getEntityManager();
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
