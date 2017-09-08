<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

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
    /** @var SaverInterface */
    private $productSaver;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param BulkSaverInterface              $productSaver
     * @param BulkSaverInterface              $productModelSaver
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function save($productModel, array $options = [])
    {
        $this->validateProductModel($productModel);

        $children = $this->findDirectChildren($productModel);
        if (empty($children)) {
            return;
        }

        $firstChild = $children[0];
        if ($firstChild instanceof VariantProductInterface) {
            $this->productSaver->saveAll($children);

            return;
        }

        if ($firstChild instanceof ProductModelInterface) {
            $this->productModelSaver->saveAll($children);

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

    /**
     * Finds the direct children of a product model wether they are product models themselves or variant products.
     *
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function findDirectChildren(ProductModelInterface $productModel): array
    {
        $children = $this->productModelRepository->findChildrenProducts($productModel);

        if (empty($children)) {
            $children = $this->productModelRepository->findChildrenProductModels($productModel);
        }

        return $children;
    }
}
