<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductModelDescendantsSaver;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Prophecy\Argument;

class ProductModelDescendantsSaverSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver
    ) {
        $this->beConstructedWith($productModelRepository, $productSaver, $productModelSaver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsSaver::class);
    }

    function it_saves_a_product_model_descendants_which_are_products(
        $productModelRepository,
        $productSaver,
        ProductModelInterface $productModel,
        VariantProductInterface $variantProduct1,
        VariantProductInterface $variantProduct2
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $productModelRepository->findChildrenProducts($productModel)
            ->willReturn([$variantProduct1, $variantProduct2]);

        $productModelRepository->findChildrenProductModels($productModel)->shouldNotBeCalled();
        $productSaver->saveAll([$variantProduct1, $variantProduct2])->shouldBeCalled();

        $this->save($productModel);
    }

    function it_saves_a_product_model_descendants_which_are_sub_product_models(
        $productModelRepository,
        $productModelSaver,
        ProductModelInterface $productModel,
        ProductModelInterface $subProductModel1,
        ProductModelInterface $subProductModel2
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $productModelRepository->findChildrenProducts($productModel)
            ->willReturn([]);

        $productModelRepository->findChildrenProductModels($productModel)
            ->willReturn([$subProductModel1, $subProductModel2]);

        $productModelSaver->saveAll([$subProductModel1, $subProductModel2])->shouldBeCalled();

        $this->save($productModel);
    }

    function it_does_not_fail_when_product_model_has_no_child(
        $productModelRepository,
        $productSaver,
        $productModelSaver,
        ProductModelInterface $productModel
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $productModelRepository->findChildrenProducts($productModel)
            ->willReturn([]);

        $productModelRepository->findChildrenProductModels($productModel)
            ->willReturn([]);
        $productModelSaver->saveAll(Argument::cetera())->shouldNotBeCalled();
        $productSaver->saveAll(Argument::cetera())->shouldNotBeCalled();

        $this->save($productModel);
    }

    function it_throws_when_we_dont_save_a_product_model(
        \stdClass $wrongObject
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', [$wrongObject]);
    }
}
