<?php

namespace KFOSOFT\Cron\Value;

use Cron\CronExpression;

class Crontab extends AbstractTypedCollection
{
    /**
     * {@inheritDoc}
     */
    protected static function getItemClass(): string
    {
        return AbstractCronJob::class;
    }

    /**
     * @param array $tab
     */
    public function setTab(array $tab): void
    {
        foreach ($tab as $name => $config) {
            $params = [
                new CronExpression($config['expression']),
                $config['command'],
            ];

            $type = CommandType::getClassNameType($config['type']);

            if (InternalCronJob::class === $type) {
                $params[] = $config['params'];
            }

            /** @var AbstractCronJob $job */
            $job = new $type(...$params);

            $job->setName($name);

            $this->add($job);
        }
    }
}
