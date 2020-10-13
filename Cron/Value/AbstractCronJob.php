<?php

namespace KFOSOFT\Cron\Value;

use Cron\CronExpression;
use DateTimeInterface;
use Exception;

abstract class AbstractCronJob
{
    const NOT_EXECUTED = 0;
    const EXECUTED     = 1;

    /**
     * @var CronExpression
     */
    protected $expression;

    /**
     * @var string
     */
    protected $command;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $lastRunTime;

    /**
     * CronJob constructor.
     *
     * @param CronExpression $expression
     * @param string         $command
     */
    public function __construct(CronExpression $expression, string $command)
    {
        $this->expression = $expression;
        $this->command    = $command;
    }

    /**
     * @return CronExpression
     */
    public function getExpression(): CronExpression
    {
        return $this->expression;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Execute command if the command is due.
     *
     * @param DateTimeInterface|string|null $currentTime Relative calculation date
     * @param string|null                   $timezone    TimeZone to use instead of the system default
     * @param array|null                    $output      If the output argument is present, then the specified array
     *                                                   will be filled with every line of output from the command. Trailing whitespace, such as \n, is not
     *                                                   included in this array. Note that if the array already contains some elements, exec will append to the end of the array.
     *                                                   If you do not want the function to append elements, call unset on the array before passing it to exec.
     *
     * @throws Exception
     *
     * @return int 0 not executed, 1 command was executed
     */
    public function executeIfNeeded($currentTime = 'now', ?string $timezone = null, ?array &$output = null): int
    {
        $nextRunTime = $this->expression->getNextRunDate($currentTime, 0, false, $timezone);

        if ($nextRunTime === $this->lastRunTime || !$this->expression->isDue($currentTime, $timezone)) {
            return self::NOT_EXECUTED;
        }

        $this->execute($output);

        $this->lastRunTime = $nextRunTime;

        return self::EXECUTED;
    }

    /**
     * @param array|null $output     If the output argument is present, then the specified array will be filled with
     * every line of output from the command. Trailing whitespace, such as \n, is not included in this array.
     * Note that if the array already contains some elements, exec will append to the end of the array.
     * If you do not want the function to append elements, call unset on the array before passing it to exec.
     *
     * @return int
     */
    abstract protected function execute(?array &$output = null): int;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
