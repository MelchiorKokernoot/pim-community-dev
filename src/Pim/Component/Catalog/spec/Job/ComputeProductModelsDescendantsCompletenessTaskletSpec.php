<?php

namespace spec\Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

class ComputeProductModelsDescendantsCompletenessTaskletSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        SaverInterface $productModelDescendantsSaver
    ) {
        $this->beConstructedWith($productModelRepository, $productModelDescendantsSaver);
    }

    function it_saves_the_product_model_on_execute(
        $productModelRepository,
        $productModelDescendantsSaver,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductModelInterface $productModel
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('product_model_code')->willReturn('tshirt_root');

        $productModelRepository->findOneByIdentifier('tshirt_root')->willReturn($productModel);
        $productModelDescendantsSaver->save($productModel);
    }
}
