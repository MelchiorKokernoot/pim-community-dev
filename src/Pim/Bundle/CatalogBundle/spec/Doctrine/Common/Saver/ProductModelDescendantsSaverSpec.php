<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductModelDescendantsSaver;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;

class ProductModelDescendantsSaverSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver
    ) {
        $this->beConstructedWith($pqbFactory, $productSaver, $productModelSaver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsSaver::class);
    }

    function it_saves_a_product_model_descendants_which_are_products(
        $pqbFactory,
        $productSaver,
        ProductModelInterface $productModel,
        ProductQueryBuilderInterface $pqb,
        ArrayCollection $productModelChildren,
        \ArrayIterator $childrenIterator,
        VariantProductInterface $variantProduct1,
        VariantProductInterface $variantProduct2
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IN_LIST, ['product_model_code'], [])
            ->shouldBeCalled();
        $pqb->execute()->willReturn($productModelChildren);

        $productModelChildren->count()->willReturn(2);
        $productModelChildren->current()->willReturn($variantProduct1);

        $productModelChildren->getIterator()->willReturn($childrenIterator);
        $childrenIterator->rewind()->shouldBeCalled();
        $childrenIterator->valid()->willReturn(true, true, false);
        $childrenIterator->current()->willReturn($variantProduct1, $variantProduct2);
        $childrenIterator->next()->shouldBeCalled();

        $productSaver->save($variantProduct1)->shouldBeCalled();
        $productSaver->save($variantProduct2)->shouldBeCalled();

        $this->save($productModel);
    }

    function it_saves_a_product_model_descendants_which_are_sub_product_models(
        $pqbFactory,
        $productModelSaver,
        ProductModelInterface $productModel,
        ProductQueryBuilderInterface $pqb,
        ArrayCollection $productModelChildren,
        \ArrayIterator $childrenIterator,
        ProductModelInterface $subProductModel1,
        ProductModelInterface $subProductModel2
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IN_LIST, ['product_model_code'], [])
            ->shouldBeCalled();
        $pqb->execute()->willReturn($productModelChildren);

        $productModelChildren->count()->willReturn(2);
        $productModelChildren->current()->willReturn($subProductModel1);

        $productModelChildren->getIterator()->willReturn($childrenIterator);
        $childrenIterator->rewind()->shouldBeCalled();
        $childrenIterator->valid()->willReturn(true, true, false);
        $childrenIterator->current()->willReturn($subProductModel1, $subProductModel2);
        $childrenIterator->next()->shouldBeCalled();

        $productModelSaver->save($subProductModel1)->shouldBeCalled();
        $productModelSaver->save($subProductModel2)->shouldBeCalled();

        $this->save($productModel);
    }

    function it_does_not_fail_when_product_model_has_no_child(
        $pqbFactory,
        $productSaver,
        $productModelSaver,
        ProductModelInterface $productModel,
        ProductQueryBuilderInterface $pqb,
        ArrayCollection $productModelChildren
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IN_LIST, ['product_model_code'], [])
            ->shouldBeCalled();
        $pqb->execute()->willReturn($productModelChildren);

        $productModelChildren->count()->willReturn(0);

        $productModelSaver->save(Argument::cetera())->shouldNotBeCalled();
        $productSaver->save(Argument::cetera())->shouldNotBeCalled();

        $this->save($productModel);
    }

    function it_throws_when_we_dont_save_a_product_model(
        \stdClass $wrongObject
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', [$wrongObject]);
    }

    function it_throws_when_a_product_model_child_is_not_a_product_nor_a_product_model(
        $pqbFactory,
        ProductModelInterface $productModel,
        ProductQueryBuilderInterface $pqb,
        ArrayCollection $productModelChildren,
        \StdClass $wrongChild
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('parent', Operators::IN_LIST, ['product_model_code'], [])
            ->shouldBeCalled();
        $pqb->execute()->willReturn($productModelChildren);

        $productModelChildren->count()->willReturn(1);
        $productModelChildren->current()->willReturn($wrongChild);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', [$productModel]);
    }
}
