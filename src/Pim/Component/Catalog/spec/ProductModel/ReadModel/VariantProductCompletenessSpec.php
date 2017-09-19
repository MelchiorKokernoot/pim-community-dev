<?php

namespace spec\Pim\Component\Catalog\ProductModel\ReadModel;

use Pim\Component\Catalog\ProductModel\ReadModel\VariantProductCompleteness;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class VariantProductCompletenessSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [
                ['ch' => 'ecommerce', 'lo' => 'en_US',  'co' => 0, 'po' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'ecommerce', 'lo' => 'fr_FR', 'co' => 1, 'po' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'print', 'lo' => 'en_US', 'co' => 1, 'po' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'print', 'lo' => 'fr_FR', 'co' => 1, 'po' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'mobile', 'lo' => 'en_US', 'co' => 0, 'po' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'mobile', 'lo' => 'fr_FR', 'co' => 1, 'po' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'ecommerce', 'lo' => 'en_US', 'co' => 1, 'po' => 'biker-jacket-polyester-m'],
                ['ch' => 'ecommerce', 'lo' => 'fr_FR', 'co' => 1, 'po' => 'biker-jacket-polyester-m'],
                ['ch' => 'print', 'lo' => 'en_US', 'co' => 0, 'po' => 'biker-jacket-polyester-m'],
                ['ch' => 'print', 'lo' => 'fr_FR', 'co' => 0, 'po' => 'biker-jacket-polyester-m'],
                ['ch' => 'mobile', 'lo' => 'en_US', 'co' => 1, 'po' => 'biker-jacket-polyester-m'],
                ['ch' => 'mobile', 'lo' => 'fr_FR', 'co' => 1, 'biker-jacket-polyester-m'],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VariantProductCompleteness::class);
    }

    function it_calculates_completenesses()
    {
        $this->completenesses()->shouldReturn([
            'ecommerce' => [
                'en_US' => 1,
                'fr_FR' => 2,
            ],
            'print' => [
                'en_US' => 1,
                'fr_FR' => 1,
            ],
            'mobile' => [
                'en_US' => 1,
                'fr_FR' => 2,
            ],
        ]);
    }

    function it_calculates_the_number_of_product()
    {
        $this->total()->shouldReturn(2);
    }

}
