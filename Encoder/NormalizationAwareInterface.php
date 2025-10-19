<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Encoder;

use Flaphl\Element\Reissue\Normalizer\DenormalizerInterface;
use Flaphl\Element\Reissue\Normalizer\NormalizerInterface;

/**
 * Encoder/decoder that is aware of normalization.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface NormalizationAwareInterface
{
    /**
     * Sets the normalizer.
     *
     * @param NormalizerInterface $normalizer
     */
    public function setNormalizer(NormalizerInterface $normalizer): void;

    /**
     * Sets the denormalizer.
     *
     * @param DenormalizerInterface $denormalizer
     */
    public function setDenormalizer(DenormalizerInterface $denormalizer): void;
}
