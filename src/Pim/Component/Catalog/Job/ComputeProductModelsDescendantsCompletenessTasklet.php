<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
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

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SaverInterface */
    private $productModelSaver;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param SaverInterface                      $productSaver
     * @param SaverInterface                      $productModelSaver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver
    ) {
        $this->pqbFactory        = $pqbFactory;
        $this->productSaver      = $productSaver;
        $this->productModelSaver = $productModelSaver;
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
        $pqb = $this->pqbFactory->create(['limit' => 200]);
        $jobParameters = $this->stepExecution->getJobParameters();
        $productModelCode = $jobParameters->get('product_model_code');

        $pqb->addFilter('parent', Operators::IN_LIST, [$productModelCode], []);
        $children = $pqb->execute();
        $firstChild = $children->current();

        if ($firstChild instanceof VariantProductInterface) {
            foreach ($children as $child) {
                $this->productSaver->save($child);
            }

            return;
        }

        if ($firstChild instanceof ProductModelInterface) {
            foreach ($children as $child) {
                $this->productModelSaver->save($child);
            }

            return;
        }

        throw new \InvalidArgumentException(sprintf(
            'Expect either a "%s" or "%s", "%s" given',
            VariantProductInterface::class,
            ProductModelInterface::class,
            get_class($firstChild)
        ));
    }
}
