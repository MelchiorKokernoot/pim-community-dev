<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ComputeProductModelDescendantsCompletenessSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorage $tokenStorage,
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepository
    ) {
        $this->beConstructedWith($tokenStorage, $jobLauncher, $jobInstanceRepository, 'compute_product_models_descendants_completeness');
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE     => 'computeProductModelDescendantsCompleteness',
            StorageEvents::POST_SAVE_ALL => 'bulkComputeProductModelDescendantsCompleteness',
        ]);
    }

    function it_computes_product_model_descendants_completeness(
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        ProductModelInterface $productModel,
        GenericEvent $event,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn($productModel);
        $productModel->getCode()->willReturn('product_model_code');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobInstanceRepository->findOneByIdentifier('compute_product_models_descendants_completeness')
            ->willReturn($jobInstance);

        $jobLauncher->launch($jobInstance, $user, ['product_model_code' => 'product_model_code'])
            ->shouldBeCalled();

        $this->computeProductModelDescendantsCompleteness($event);
    }

    function it_bulk_computes_product_model_descendants_completeness(
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        GenericEvent $event,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn([$productModel1, $productModel2]);
        $productModel1->getCode()->willReturn('jambon');
        $productModel2->getCode()->willReturn('fromage');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobInstanceRepository->findOneByIdentifier('compute_product_models_descendants_completeness')
            ->willReturn($jobInstance);

        $jobLauncher->launch($jobInstance, $user, ['product_model_code' => 'jambon'])
            ->shouldBeCalled();

        $jobLauncher->launch($jobInstance, $user, ['product_model_code' => 'fromage'])
            ->shouldBeCalled();

        $this->bulkComputeProductModelDescendantsCompleteness($event);
    }

    function it_does_not_launch_a_job_if_it_is_not_a_product_model(
        $jobLauncher,
        GenericEvent $event,
        \stdClass $wrongObject
    ) {
        $event->getSubject()->willReturn($wrongObject);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->computeProductModelDescendantsCompleteness($event);
    }

    function it_does_not_launch_multiple_jobs_if_it_is_not_an_array(
        $jobLauncher,
        GenericEvent $event,
        \stdClass $wrongObject
    ) {
        $event->getSubject()->willReturn($wrongObject);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->bulkComputeProductModelDescendantsCompleteness($event);
    }

    function it_does_not_launch_multiple_jobs_if_it_is_not_a_product_model(
        $jobLauncher,
        GenericEvent $event,
        \stdClass $wrongObject
    ) {
        $event->getSubject()->willReturn([$wrongObject]);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->bulkComputeProductModelDescendantsCompleteness($event);
    }
}
