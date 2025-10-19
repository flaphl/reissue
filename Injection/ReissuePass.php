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
 * Compiler pass for registering encoders and normalizers.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class ReissuePass
{
    public const TAG_ENCODER = 'reissue.encoder';
    public const TAG_DECODER = 'reissue.decoder';
    public const TAG_NORMALIZER = 'reissue.normalizer';
    public const TAG_DENORMALIZER = 'reissue.denormalizer';

    /**
     * Processes tagged services and registers them with the reissue service.
     *
     * @param object $container The service container
     */
    public function process(object $container): void
    {
        // This is a placeholder for dependency injection integration
        // The actual implementation would depend on the DI container being used
        
        // Example for a hypothetical container:
        // $encoders = $container->findTaggedServices(self::TAG_ENCODER);
        // $normalizers = $container->findTaggedServices(self::TAG_NORMALIZER);
        // 
        // $reissueDefinition = $container->getDefinition('reissue');
        // $reissueDefinition->setArgument(0, $normalizers);
        // $reissueDefinition->setArgument(1, $encoders);
    }

    /**
     * Registers default encoders.
     *
     * @param object $container The service container
     */
    public function registerDefaultEncoders(object $container): void
    {
        // Register JsonEncoder
        // Register XmlEncoder
        // Tag them with self::TAG_ENCODER
    }

    /**
     * Registers default normalizers.
     *
     * @param object $container The service container
     */
    public function registerDefaultNormalizers(object $container): void
    {
        // Register ArrayNormalizer
        // Register DateTimeNormalizer
        // Register ObjectNormalizer or MetadataAwareObjectNormalizer
        // Tag them with self::TAG_NORMALIZER
    }
}
