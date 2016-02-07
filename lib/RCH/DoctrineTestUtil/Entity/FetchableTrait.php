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

/**
 * Generates abstract entity mappings used as mock in unit tests.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
trait FetchableTrait
{
    /**
     * Fetch results.
     *
     * @param array $params
     *
     * @return array
     */
    public function fetch(array $params)
    {
        $countWheres = 0;
        $selectStatement = '';

        $repository = $this->em->getRepository($this->entityName);
        $query = $repository->createQueryBuilder('t');

        foreach ($params as $key => $value) {
            $selectStatement .= 't.'.$key;
            if (next($params)) {
                $selectStatement .= ', ';
            }
        }

        $query->select($selectStatement);

        foreach ($params as $key => $value) {
            $statement = $countWheres == 0 ? 'where' : 'andWhere';
            $query->$statement(sprintf('t.%s = :%s', $key, $key));
            $query->setParameter($key, $value);

            ++$countWheres;
        }

        $query = $query->getQuery();

        return $query->getResult();
    }
}
