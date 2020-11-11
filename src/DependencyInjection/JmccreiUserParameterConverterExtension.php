<?php
/**
 * Copyright (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace Jmccrei\UserParameterConverter\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * Class JmccreiUserParameterConverterExtension
 * @package Jmccrei\UserParameterConverter\DependencyInjection
 */
class JmccreiUserParameterConverterExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(
            implode(DIRECTORY_SEPARATOR, [
                __DIR__,
                '..',
                'Resources',
                'config'
            ])
        ));

        $loader->load('services.yaml');
    }
}