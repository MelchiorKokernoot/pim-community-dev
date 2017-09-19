<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Normalizer;

use Pim\Component\Catalog\ProductModel\ReadModel\VariantProductCompleteness;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a VariantProductCompleteness, this normalizer must be used by the internal API.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductCompletenessNormalizer implements NormalizerInterface
{
    private const FIELD_COMPLETENESS = 'completenesses';
    private const FIELD_TOTAL = 'total';
    private const FORMAT = 'internal_api';

    /**
     * {@inheritdoc}
     *
     * @param VariantProductCompletenessNormalizer $variantProductCompleteness
     */
    public function normalize($variantProductCompleteness, $format = null, array $context = array()): array
    {
        $normalizedCompletenesses = [
            self::FIELD_COMPLETENESS => $variantProductCompleteness->completenesses(),
            self::FIELD_TOTAL => $variantProductCompleteness->total(),
        ];

        return $normalizedCompletenesses;
    }

    /**
     * {@inheritdoc}
     *
     * @param VariantProductCompletenessNormalizer $variantProductCompleteness
     */
    public function supportsNormalization($variantProductCompleteness, $format = null): bool
    {
        return
            $variantProductCompleteness instanceof VariantProductCompleteness &&
            self::FORMAT === $format
        ;
    }
}
