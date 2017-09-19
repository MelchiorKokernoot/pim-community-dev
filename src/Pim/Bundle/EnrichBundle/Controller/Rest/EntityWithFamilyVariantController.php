<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\EnrichBundle\Normalizer\EntityWithFamilyVariantNormalizer;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithFamilyVariantController
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var EntityWithFamilyVariantNormalizer */
    private $entityWithFamilyVariantNormalizer;

    /**
     * @param ProductModelRepositoryInterface   $entityWithFamilyVariantRepository
     * @param EntityWithFamilyVariantNormalizer $entityWithFamilyVariantNormalizer
     */
    public function __construct(
        ProductModelRepositoryInterface $entityWithFamilyVariantRepository,
        EntityWithFamilyVariantNormalizer $entityWithFamilyVariantNormalizer
    ) {
        $this->productModelRepository            = $entityWithFamilyVariantRepository;
        $this->entityWithFamilyVariantNormalizer = $entityWithFamilyVariantNormalizer;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function childrenAction(Request $request)
    {
        $identifier = $request->query->get('identifier');
        $parent = $this->productModelRepository->find($identifier);
        if (null === $parent) {
            throw new NotFoundHttpException(sprintf('ProductModel with id "%s" not found', $identifier));
        }

        $children = $this->productModelRepository->findChildrenProductModels($parent);
        if (empty($children)) {
            $children = $this->productModelRepository->findChildrenProducts($parent);
        }

        $normalizedChildren = [];
        foreach ($children as $child) {
            if (!$child instanceof ProductModelInterface && !$child instanceof VariantProductInterface) {
                throw new \LogicException(sprintf(
                    'Child of a product model must be of class "%s" or "%s", "%s" received.',
                    ProductModelInterface::class,
                    VariantProductInterface::class,
                    get_class($child)
                ));
            }

            $normalizedChildren[] = $this->entityWithFamilyVariantNormalizer->normalize($child, 'internal_api');
        }

        return new JsonResponse($normalizedChildren);
    }
}
