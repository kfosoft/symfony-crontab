<?php

namespace KFOSOFT\Cron\Command;

use Exception;
use KFOSOFT\Cron\Value\AbstractCronJob;
use KFOSOFT\Cron\Value\Crontab;
use KFOSOFT\Cron\Value\InternalCronJob;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Wrep\Daemonizable\Command\EndlessCommand;

class CronCommand extends EndlessCommand implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Crontab
     */
    private $crontab;

    /**
     * CronCommand constructor.
     *
     * @param string|null $name
     * @param Crontab     $crontab
     */
    public function __construct(string $name = null, Crontab $crontab)
    {
        parent::__construct($name);

        $this->crontab = $crontab;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cron:daemon')
            ->setDescription('Cron daemon. To configure crontab see package crontab(Example: config/packages/crontab.yml) configuration')
            ->setTimeout(60);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment> - Start cron iteration</comment>', OutputInterface::VERBOSITY_VERY_VERBOSE);

        /** @var AbstractCronJob $job */
        foreach ($this->crontab as $job) {
            $output->writeln(sprintf('%s - Execute command "<comment>%s</comment>" "<info>%s</info>".', PHP_EOL, $job->getName(), $job->getCommand()), OutputInterface::VERBOSITY_VERBOSE);

            $outputJob = [];

            if ($job instanceof InternalCronJob) {
                $job->setApplication($this->getApplication());
                $job->setVerbosity($output->getVerbosity());
            }

            try {
                if (AbstractCronJob::EXECUTED === $job->executeIfNeeded('now', null, $outputJob)) {
                    $output->writeln(sprintf('The command "<info>%s</info>" was executed. Expression: <comment>%s</comment>', $job->getCommand(), $job->getExpression()->getExpression()), OutputInterface::VERBOSITY_VERBOSE);
                    $output->writeln(sprintf('The output "<comment>%s</comment>" of command "<info>%s</info>".', json_encode($outputJob), $job->getCommand()), OutputInterface::VERBOSITY_VERY_VERBOSE);
                } else {
                    $output->writeln(sprintf('The command "<info>%s</info>" wasn\'t executed because now it\'s not needed. Expression: <comment>%s</comment>', $job->getCommand(), $job->getExpression()->getExpression()), OutputInterface::VERBOSITY_VERBOSE);
                }
            } catch (Throwable $e) {
                $output->writeln(sprintf('Cron job has failed with message "<error>%s</error>".', $e->getMessage()));
                $this->logger->critical($e->getMessage(), $e->getTrace());
            }

        }

        $output->writeln(sprintf('%s<comment> - End cron iteration</comment> %s', PHP_EOL, PHP_EOL), OutputInterface::VERBOSITY_VERY_VERBOSE);
    }
}
