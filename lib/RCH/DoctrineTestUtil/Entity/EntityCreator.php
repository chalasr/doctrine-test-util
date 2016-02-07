<?php

/*
 * This file is part of the RCH package.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this recorder code.
 */

namespace RCH\DoctrineTestUtil\Entity;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\EntityGenerator;

/**
 * Generates abstract entity mappings used as mock in unit tests.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class EntityCreator
{
    /** @static @var string */
    protected static $name;

    /** @static @var string */
    protected static $path;

    /** @static @var array */
    protected static $fields;

    /** @static @var string */
    protected static $annotationPrefix;

    /**
     * Creates an entity.
     *
     * @static
     */
    public static function create($name, $path, array $fields, $annotationPrefix = 'ORM\\')
    {
        self::$name                         = $name;
        self::$path                         = $path;
        self::$fields                     = $fields;
        self::$annotationPrefix = $annotationPrefix;

        $class = new ClassMetadataInfo(self::$name);
        $class->mapField(array('fieldName' => 'id', 'type' => 'integer', 'id' => true));
        $class->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);

        foreach (self::$fields as $field) {
            $class->mapField($field);
        }

        file_put_contents(self::$path, self::getEntityGenerator()->generateEntityClass($class));
    }

    /**
     * Drop the entity.
     */
    public static function drop()
    {
        unlink(self::$path);
    }

    /**
     * Get path.
     *
     * @return string
     */
    public static function getPath()
    {
        return self::$path;
    }

    /**
     * Get namespace.
     *
     * @return string
     */
    public static function getNamespace()
    {
        return self::$name;
    }

    /**
     * Get entity generator.
     *
     * @return EntityGenerator
     */
    protected static function getEntityGenerator()
    {
        $entityGenerator = new EntityGenerator();
        $entityGenerator->setGenerateAnnotations(true);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(true);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix(self::$annotationPrefix);

        return $entityGenerator;
    }
}
