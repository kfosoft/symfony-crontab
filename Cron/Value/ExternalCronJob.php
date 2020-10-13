<?php

namespace KFOSOFT\Cron\Value;

class ExternalCronJob extends AbstractCronJob
{
    /**
     * {@inheritdoc}
     */
    protected function execute(?array &$output = null): int
    {
        exec($this->command, $output, $return);

        return $return;
    }
}
