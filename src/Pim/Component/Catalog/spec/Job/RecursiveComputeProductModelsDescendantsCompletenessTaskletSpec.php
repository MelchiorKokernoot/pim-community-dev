<?php

namespace spec\Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

class RecursiveComputeProductModelsDescendantsCompletenessTaskletSpec extends ObjectBehavior
{
    function let(ProductModelRepositoryInterface $productModelRepository, BulkSaverInterface $productSaver)
    {
        $this->beConstructedWith($productModelRepository, $productSaver);
    }

    function it_recursively_computes_children_completeness(
        $productModelRepository,
        $productSaver,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $subProductModel1,
        ProductModelInterface $subProductModel2,
        ProductModelInterface $subSubProductModel1,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('product_model_codes')->willReturn(['tshirt_root']);

        $productModelRepository->findBy(['code' => ['tshirt_root']])->willReturn([$rootProductModel]);

        $productModelRepository->findChildrenProductModels($rootProductModel)->willReturn([
            $subProductModel1,
            $subProductModel2
        ]);

        $productModelRepository->findChildrenProductModels($subProductModel1)->willReturn([$subSubProductModel1]);
        $productModelRepository->findChildrenProductModels($subProductModel2)->willReturn([]);
        $productModelRepository->findChildrenProductModels($subSubProductModel1)->willReturn([]);

        $productModelRepository->findChildrenProducts($subProductModel2)->willReturn([$product1, $product2]);
        $productModelRepository->findChildrenProducts($subSubProductModel1)->willReturn([$product3]);

        $productSaver->saveAll([$product1, $product2])->shouldBeCalled();
        $productSaver->saveAll([$product3])->shouldBeCalled();

        $this->execute();
    }
}
