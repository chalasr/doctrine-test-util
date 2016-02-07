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
     * @return DatabaseConnection
     */
    public static function create()
    {
        $entityManager = self::getEntityManager();
        // $pdo = $entityManager->getConnection()->getWrappedConnection();
        $entityManager->clear();

        $tool = new SchemaTool($entityManager);
        $classes = $entityManager->getMetaDataFactory()->getAllMetaData();

        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        /* createDefaultDBConnection($pdo, $GLOBALS['db_user']); // Pass it to phpunit */

        return new self();
    }

    /**
     * Get entity manager.
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
}
