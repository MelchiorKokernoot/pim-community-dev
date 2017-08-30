<?php

namespace spec\Akeneo\Bundle\BatchQueueBundle\Hydrator;

use Akeneo\Bundle\BatchQueueBundle\Hydrator\JobExecutionMessageHydrator;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class JobExecutionMessageHydratorSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $entityManager,
        SimpleFactoryInterface $jobExecutionMessageFactory,
        Connection $connection,
        MySqlPlatform $platform
    ) {
        $entityManager->getConnection()->willReturn($connection);
        $connection->getDatabasePlatform()->willReturn($platform);
        $this->beConstructedWith($entityManager, $jobExecutionMessageFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JobExecutionMessageHydrator::class);
    }

    function it_hydrates_a_job_execution_message(
        $jobExecutionMessageFactory,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageFactory->create()->willReturn($jobExecutionMessage);

        $jobExecutionMessage->setId(1)->willReturn($jobExecutionMessage);
        $jobExecutionMessage->setJobExecutionId(2)->willReturn($jobExecutionMessage);
        $jobExecutionMessage->setOptions(['env' => 'test'])->willReturn($jobExecutionMessage);
        $jobExecutionMessage->setConsumer('consumer_name')->willReturn($jobExecutionMessage);
        $jobExecutionMessage->setCreateTime(Argument::type(\DateTime::class))->shouldBeCalled();
        $jobExecutionMessage->setUpdatedTime(Argument::type(\DateTime::class))->shouldBeCalled();

        $row = [
            'id' => '1',
            'job_execution_id' => '2',
            'options' => '{"env": "test"}',
            'create_time' => '2017-09-19 13:30:00',
            'updated_time' => '2017-09-19 13:30:15',
            'consumer' =>  'consumer_name',
        ];

        $this->hydrate($row)->shouldReturn($jobExecutionMessage);
    }

    function it_hydrates_a_job_execution_message_with_mandatory_field_only(
        $jobExecutionMessageFactory,
        JobExecutionMessage $jobExecutionMessage
    ) {
        $jobExecutionMessageFactory->create()->willReturn($jobExecutionMessage);

        $jobExecutionMessage->setId(1)->willReturn($jobExecutionMessage);
        $jobExecutionMessage->setJobExecutionId(2)->willReturn($jobExecutionMessage);
        $jobExecutionMessage->setOptions(['env' => 'test'])->willReturn($jobExecutionMessage);
        $jobExecutionMessage->setCreateTime(Argument::type(\DateTime::class))->shouldBeCalled();
        $jobExecutionMessage->setConsumer('consumer_name')->shouldNotBeCalled();
        $jobExecutionMessage->setUpdatedTime(Argument::type(\DateTime::class))->shouldNotBeCalled();

        $row = [
            'id' => '1',
            'job_execution_id' => '2',
            'options' => '{"env": "test"}',
            'create_time' => '2017-09-19 13:30:00',
            'updated_time' => null,
            'consumer' =>  null,
        ];

        $this->hydrate($row)->shouldReturn($jobExecutionMessage);
    }

    function it_throws_an_exception_if_a_property_is_missing() {
        $row = [
            'id' => '1',
            'job_execution_id' => '2',
            'options' => '{"env": "test"}',
            'create_time' => '2017-09-19 13:30:00',
            'updated_time' => null,
        ];

        $this
            ->shouldThrow(new MissingOptionsException('The required option "consumer" is missing.'))
            ->during('hydrate', [$row]);
    }
}
