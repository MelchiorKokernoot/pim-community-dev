<?php

namespace Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RecursiveComputeProductModelsDescendantsCompletenessTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var BulkSaverInterface */
    private $productSaver;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param BulkSaverInterface                  $productSaver
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productSaver = $productSaver;
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
    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $productModelCodes = $jobParameters->get('product_model_codes');
        $productModels = $this->productModelRepository->findBy(['code' => $productModelCodes]);

        foreach ($productModels as $productModel) {
            $this->computeChildrenCompleteness($productModel);
        }
    }

    /**
     * @param ProductModelInterface $productModel
     */
    private function computeChildrenCompleteness(ProductModelInterface $productModel): void
    {
        $childrenProductModels = $this->productModelRepository->findChildrenProductModels($productModel);
        if (!empty($childrenProductModels)) {
            foreach ($childrenProductModels as $childProductModel) {
                $this->computeChildrenCompleteness($childProductModel);
            }

            return;
        }

        $childrenProducts = $this->productModelRepository->findChildrenProducts($productModel);
        if (!empty($childrenProducts)) {
            $this->productSaver->saveAll($childrenProducts);
        }
    }
}
