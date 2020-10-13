<?php

namespace KFOSOFT\Cron\DependencyInjection;

use Cron\CronExpression;
use KFOSOFT\Cron\Value\CronJob;
use KFOSOFT\Cron\Value\Crontab;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class CrontabExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $container->register(Crontab::class, Crontab::class)
            ->addMethodCall('setTab', [$config['tab']]);

        $locator = new FileLocator(__DIR__ . '/../Resources/config/');
        $loader  = new YamlFileLoader($container, $locator);

        $loader->load('services.yaml');
    }
}
