<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;

/**
 * This StepExecution retrieves children of the given product model,
 * then save them (to reindex, compute completeness...)
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeProductModelsDescendantsCompletenessTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var SaverInterface */
    private $productModelDescendantsSaver;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param SaverInterface                  $productModelDescendantsSaver
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        SaverInterface $productModelDescendantsSaver
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productModelDescendantsSaver = $productModelDescendantsSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $productModelCode = $jobParameters->get('product_model_code');
        $productModel = $this->productModelRepository->findOneByIdentifier($productModelCode);

        $this->productModelDescendantsSaver->save($productModel);
    }
}
