<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Injection;

/**
 * Compiler pass for processing attribute metadata.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class AttributeMetadataPass
{
    public const TAG_METADATA_AWARE = 'reissue.metadata_aware';

    /**
     * Processes services that need attribute metadata loading.
     *
     * @param object $container The service container
     */
    public function process(object $container): void
    {
        // This is a placeholder for dependency injection integration
        // The actual implementation would depend on the DI container being used
        
        // Example for a hypothetical container:
        // $services = $container->findTaggedServices(self::TAG_METADATA_AWARE);
        //
        // foreach ($services as $id => $tags) {
        //     $definition = $container->getDefinition($id);
        //     // Inject AttributeLoader
        //     $definition->addMethodCall('setAttributeLoader', [new Reference('attribute.loader')]);
        // }
    }

    /**
     * Registers the attribute loader service.
     *
     * @param object $container The service container
     */
    public function registerAttributeLoader(object $container): void
    {
        // Register AttributeLoader as a service
        // $container->register('attribute.loader', AttributeLoader::class);
    }
}
