<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This subscriber listens to PostSave events on Product Models.
 *
 * When a product model is saved, it launches a job responsible to save its direct children:
 * This way they will be indexed and their completeness will be computed (it they are products).
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeProductModelDescendantsCompletenessSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var string */
    private $jobName;

    /**
     * @param TokenStorage          $tokenStorage
     * @param SimpleJobLauncher     $jobLauncher
     * @param JobInstanceRepository $jobInstanceRepository
     * @param string                $jobName
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        string $jobName
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobName = $jobName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE     => 'computeProductModelDescendantsCompleteness',
            StorageEvents::POST_SAVE_ALL => 'bulkComputeProductModelDescendantsCompleteness',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function computeProductModelDescendantsCompleteness(GenericEvent $event): void
    {
        $productModel = $event->getSubject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        $this->jobLauncher->launch($jobInstance, $user, ['product_model_code' => $productModel->getCode()]);
    }

    /**
     * @param GenericEvent $event
     */
    public function bulkComputeProductModelDescendantsCompleteness(GenericEvent $event): void
    {
        $productModels = $event->getSubject();
        if (!is_array($productModels)) {
            return;
        }

        if (!current($productModels) instanceof ProductModelInterface) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        foreach ($productModels as $productModel) {
            $this->jobLauncher->launch($jobInstance, $user, ['product_model_code' => $productModel->getCode()]);
        }
    }
}
