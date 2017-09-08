<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

/**
 * This class's responsibility is to call save on the direct children of a product model.
 *
 * This call, will in turn trigger a save on the children's direct children, etc (thanks to our POST_SAVE event).
 * In the end, we should have called save on each element of the product model subtree.
 *
 * This ensures two things:
 * - Recalculate the completeness for each *variant product* belonging to the subtree
 * - Trigger the reindexing of the model and variant product belonging to the subtree
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelDescendantsSaver implements SaverInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SaverInterface */
    private $productModelSaver;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param SaverInterface                      $productSaver
     * @param SaverInterface                      $productModelSaver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver
    ) {
        $this->pqbFactory        = $pqbFactory;
        $this->productSaver      = $productSaver;
        $this->productModelSaver = $productModelSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function save($productModel, array $options = [])
    {
        $this->validateProductModel($productModel);

        $pqb = $this->pqbFactory->create();
        $pqb->addFilter('parent', Operators::IN_LIST, [$productModel->getCode()], []);
        $children = $pqb->execute();
        if (0 === $children->count()) {
            return;
        }

        $firstChild = $children->current();

        if ($firstChild instanceof VariantProductInterface) {
            foreach ($children as $child) {
                $this->productSaver->save($child);
            }

            return;
        }

        if ($firstChild instanceof ProductModelInterface) {
            foreach ($children as $child) {
                $this->productModelSaver->save($child);
            }

            return;
        }

        throw new \InvalidArgumentException(sprintf(
            'Expect either a "%s" or "%s", "%s" given',
            VariantProductInterface::class,
            ProductModelInterface::class,
            get_class($firstChild)
        ));
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @throws \InvalidArgumentException
     */
    protected function validateProductModel($productModel): void
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    ProductModelInterface::class,
                    get_class($productModel)
                )
            );
        }
    }
}
