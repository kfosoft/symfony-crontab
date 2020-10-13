<?php

namespace KFOSOFT\Cron\Value;

use Cron\CronExpression;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class InternalCronJob extends AbstractCronJob
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var ArrayInput
     */
    private $input;

    /**
     * @var int
     */
    private $verbosity;

    /**
     * InternalCronJob constructor.
     *
     * @param CronExpression $expression
     * @param string         $command
     * @param array          $params
     */
    public function __construct(CronExpression $expression, string $command, array $params = [])
    {
        parent::__construct($expression, $command);

        $config = [];

        foreach ($params as $name => $value) {
            $config[str_replace('_', '-', $name)] = $value;
        }

        $this->input = new ArrayInput($config);
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    /**
     * @param int $verbosity
     */
    public function setVerbosity(int $verbosity): void
    {
        $this->verbosity = $verbosity;
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    protected function execute(?array &$output = null): int
    {
        $command = $this->application->find($this->command);

        $output = new BufferedOutput();
        $output->setVerbosity($this->verbosity);

        $result = $command->run($this->input, $output);

        $output = [$output->fetch()];

        return $result;
    }
}
