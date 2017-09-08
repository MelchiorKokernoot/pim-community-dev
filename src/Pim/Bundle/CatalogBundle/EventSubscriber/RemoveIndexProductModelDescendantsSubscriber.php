<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Index product models descendants in the search engine.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveIndexProductModelDescendantsSubscriber implements EventSubscriberInterface
{
    /** @var RemoverInterface */
    private $productModelDescendantsRemover;

    /**
     * @param RemoverInterface $productModelDescendantsRemover
     */
    public function __construct(RemoverInterface $productModelDescendantsRemover)
    {
        $this->productModelDescendantsRemover = $productModelDescendantsRemover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE   => 'deleteProductModelDescendants',
        ];
    }

    /**
     * Remove one product model descendants from the index.
     *
     * @param RemoveEvent $event
     */
    public function deleteProductModelDescendants(RemoveEvent $event): void
    {
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        $this->productModelDescendantsRemover->remove($productModel);
    }
}
