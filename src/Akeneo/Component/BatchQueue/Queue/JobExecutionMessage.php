<?php

declare(strict_types=1);

namespace Akeneo\Component\BatchQueue\Queue;

/**
 * Object representing the message pushed into a queue to process a job execution asynchronously.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionMessage
{
    /** @var integer */
    protected $id;

    /** @var int */
    protected $jobExecutionId;

    /** @var string */
    protected $consumer;

    /** @var \DateTime */
    protected $createTime;

    /** @var \DateTime */
    protected $updatedTime;

    /** @var array */
    protected $options = [];

    public function __construct()
    {
        $this->createTime = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return JobExecutionMessage
     */
    public function setId(int $id): JobExecutionMessage
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getJobExecutionId(): ?int
    {
        return $this->jobExecutionId;
    }

    /**
     * @param int $jobExecutionId
     *
     * @return JobExecutionMessage
     */
    public function setJobExecutionId(int $jobExecutionId): JobExecutionMessage
    {
        $this->jobExecutionId = $jobExecutionId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getConsumer(): ?string
    {
        return $this->consumer;
    }

    /**
     * @param string $consumer
     *
     * @return JobExecutionMessage
     */
    public function setConsumer(string $consumer): JobExecutionMessage
    {
        $this->consumer = $consumer;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getCreateTime(): \DateTime
    {
        return $this->createTime;
    }

    /**
     * @param \DateTime|null $createTime
     *
     * @return JobExecutionMessage
     */
    public function setCreateTime(\DateTime $createTime): JobExecutionMessage
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getUpdatedTime(): ?\DateTime
    {
        return $this->updatedTime;
    }

    /**
     * @param \DateTime|null $updatedTime
     *
     * @return JobExecutionMessage
     */
    public function setUpdatedTime(\DateTime $updatedTime): JobExecutionMessage
    {
        $this->updatedTime = $updatedTime;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return JobExecutionMessage
     */
    public function setOptions(array $options): JobExecutionMessage
    {
        $this->options = $options;

        return $this;
    }
}
