<?php

namespace spec\Pim\Component\Catalog\ProductModel\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\ProductModel\Normalizer\VariantProductCompletenessNormalizer;
use Pim\Component\Catalog\ProductModel\ReadModel\VariantProductCompleteness;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VariantProductCompletenessNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(VariantProductCompletenessNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_product_variant_completeness(VariantProductCompleteness $completeness)
    {
        $completeness->total()->willReturn(40);
        $completeness->completenesses()->willReturn([
            'ecommerce' => [
                'en_US' => 0,
                'fr_FR' => 1,
            ],
            'print' => [
                'en_US' => 0,
                'fr_FR' => 0,
            ],
        ]);

        $this->normalize($completeness)->shouldReturn([
            'completenesses' => [
                'ecommerce' => [
                    'en_US' => 0,
                    'fr_FR' => 1,
                ],
                'print' => [
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
            ],
            'total' => 40,
        ]);
    }

    function it_only_normalizes_variant_product_completeness(
        VariantProductCompleteness $completeness
    ) {
        $this->supportsNormalization($completeness, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($completeness, 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
    }
}
