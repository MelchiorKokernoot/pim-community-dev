<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchQueueBundle\Command;

use Akeneo\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Provider\NodeProviderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

/**
 * This command is a daemon to consume job execution messages and launch the associated job execution in background.
 * A command can only execute one single job at a time.
 * The command will not launch any other jobs until the current job is not finished.
 *
 * If you want to execute several jobs in parallel, you have to run several daemons.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobQueueConsumerCommand extends ContainerAwareCommand
{
    /** Interval in seconds before checking if the job is still running. */
    const HEALTH_CHECK_INTERVAL = 5;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:batch:job-queue-consumer')
            ->setDescription('Launch a daemon that will consume job execution messages and launch the associated job execution in backgrounds')
            ->addArgument('consumer_name', InputArgument::REQUIRED, 'Consumer name')
            ->addOption('run-once', null, InputOption::VALUE_NONE, 'Launch only one job execution and stop the daemon once the job execution is finished');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $systemId = $this->getNodeProvider()->getNode();
        $consumerName = sprintf('%s_%s', $systemId, $input->getArgument('consumer_name'));

        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s/bin/console', $this->getProjectDir());

        $processBuilder = ProcessBuilder::create();
        $processBuilder
            ->setPrefix([$pathFinder->find(), $console, 'akeneo:batch:job'])
            ->setTimeout(null);

        do {
            try {
                $jobExecutionMessage = $this->getQueue()->consume($consumerName);

                $processBuilder->setArguments($this->getArguments($jobExecutionMessage));
                $process = $processBuilder->getProcess();

                $output->writeln(sprintf('Launching job execution "%s".', $jobExecutionMessage->getJobExecutionId()));
                $output->writeln(sprintf('Command line: %s', $process->getCommandLine()));

                $process->start();

                do {
                    $this->getJobExecutionManager()->updateHealthCheck($jobExecutionMessage);
                    // TODO: standard and error output in a dedicated file for each job execution
                    $output->write($process->getIncrementalOutput());
                    $errOutput->write($process->getIncrementalErrorOutput());
                    sleep(self::HEALTH_CHECK_INTERVAL);
                } while ($process->isRunning());

                $exitStatus = $this->getJobExecutionManager()->getExitStatus($jobExecutionMessage);
                if ($exitStatus->isRunning()) {
                    $this->getJobExecutionManager()->markAsFailed($jobExecutionMessage);
                }

                $output->write($process->getIncrementalOutput());
                $errOutput->write($process->getIncrementalErrorOutput());

                $output->writeln(sprintf('Job execution "%s" is finished.', $jobExecutionMessage->getJobExecutionId()));
            } catch (\Throwable $t) {
                $errOutput->writeln(sprintf('An error occurred: %s', $t->getMessage()));
                $errOutput->writeln($t->getTraceAsString());
            }
        } while (false === $input->getOption('run-once'));
    }

    /**
     * Return all the arguments of the command to execute.
     * Options are considered as arguments.
     *
     * @param JobExecutionMessage $jobExecutionMessage
     *
     * @return array
     */
    protected function getArguments(JobExecutionMessage $jobExecutionMessage): array
    {
        $jobInstanceCode = $this->getJobExecutionMessageRepository()->getJobInstanceCode($jobExecutionMessage);

        $arguments = [
            $jobInstanceCode,
            $jobExecutionMessage->getJobExecutionId(),
        ];

        foreach ($jobExecutionMessage->getOptions() as $optionsName => $optionValue) {
            if (true === $optionValue) {
                $arguments[] = sprintf('--%s', $optionValue);
            }
            if (false !== $optionValue) {
                $arguments[] = sprintf('--%s=%s', $optionsName, $optionValue);
            }
        }

        return $arguments;
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection();
    }

    /**
     * @return NodeProviderInterface
     */
    protected function getNodeProvider(): NodeProviderInterface
    {
        return $this->getContainer()->get('ramsey.uuid.provider.node.system_node_provider');
    }

    /**
     * @return JobExecutionQueueInterface
     */
    protected function getQueue(): JobExecutionQueueInterface
    {
        return $this->getContainer()->get('akeneo_batch_queue.queue.database_job_execution_queue');
    }

    /**
     * @return JobExecutionMessageRepository
     */
    protected function getJobExecutionMessageRepository(): JobExecutionMessageRepository
    {
        return $this->getContainer()->get('akeneo_batch_queue.queue.job_execution_message_repository');
    }

    /**
     * @return JobExecutionManager
     */
    protected function getJobExecutionManager(): JobExecutionManager
    {
        return $this->getContainer()->get('akeneo_batch_queue.manager.job_execution_manager');
    }

    /**
     * @return string
     */
    private function getProjectDir(): string
    {
        return $this->getContainer()->getParameter('kernel.project_dir');
    }
}
