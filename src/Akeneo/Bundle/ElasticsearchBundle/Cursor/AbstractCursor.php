<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Common logic shared by all our cursors.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCursor implements CursorInterface
{
    /** @var Client */
    protected $esClient;

    /** @var CursorableRepositoryInterface */
    protected $repository;

    /** @var array */
    protected $esQuery;

    /** @var string */
    protected $indexType;

    /** @var array */
    protected $items = [];

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $count;

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Get the next items (hydrated from doctrine repository).
     * This method should be called by the constructor of the cursors.
     *
     * @param array $esQuery
     *
     * @return array
     */
    protected function getNextItems(array $esQuery)
    {
        $identifiers = $this->getNextIdentifiers($esQuery);
        if (empty($identifiers)) {
            return [];
        }

        $hydratedItems = $this->repository->getItemsFromIdentifiers($identifiers);

        $orderedItems = [];

        foreach ($identifiers as $identifier) {
            foreach ($hydratedItems as $hydratedItem) {
                if ($identifier === $hydratedItem->getIdentifier()) {
                    $orderedItems[] = $hydratedItem;
                    break;
                }
            }
        }

        return $orderedItems;
    }

    /**
     * Get the next identifiers from the Elasticsearch query
     *
     * @param array $esQuery
     *
     * @return array
     */
    abstract protected function getNextIdentifiers(array $esQuery);
}
