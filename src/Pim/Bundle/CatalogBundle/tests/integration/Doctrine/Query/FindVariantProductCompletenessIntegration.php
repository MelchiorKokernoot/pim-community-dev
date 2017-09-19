<?php
declare(strict_types=1);

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class FindVariantProductCompletenessIntegration extends TestCase
{
    public function testReturnsCompletenessesForASubProductModel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-braided-hat');
        $result = ($this->get('pim_catalog.doctrine.query.find_variant_product_completeness'))($productModel);

        $this->assertEquals([
            'ecommerce' => [
                'de_DE' => 0,
                'en_US' => 2,
                'fr_FR' => 0,
            ],
            'mobile' => [
                'de_DE' => 0,
                'en_US' => 2,
                'fr_FR' => 0,
            ],
            'print' => [
                'de_DE' => 0,
                'en_US' => 2,
                'fr_FR' => 0,
            ],
        ], $result->completenesses());
        $this->assertEquals(2, $result->total());
    }

    public function testReturnsCompletenessesForARootProductModel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-biker-jacket');
        $result = ($this->get('pim_catalog.doctrine.query.find_variant_product_completeness'))($productModel);

        $this->assertEquals([
            'ecommerce' => [
                'de_DE' => 0,
                'en_US' => 0,
                'fr_FR' => 0,
            ],
            'mobile' => [
                'de_DE' => 0,
                'en_US' => 0,
                'fr_FR' => 0,
            ],
            'print' => [
                'de_DE' => 0,
                'en_US' => 0,
                'fr_FR' => 0,
            ],
        ], $result->completenesses());
        $this->assertEquals(6, $result->total());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }
}
