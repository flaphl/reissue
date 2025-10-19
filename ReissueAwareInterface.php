<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue;

/**
 * Interface for objects that are aware of the reissue process.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
interface ReissueAwareInterface
{
    /**
     * Called before the object is serialized.
     *
     * @param array $context The serialization context
     */
    public function beforeReissue(array $context = []): void;

    /**
     * Called after the object is deserialized.
     *
     * @param array $context The deserialization context
     */
    public function afterDeissue(array $context = []): void;
}
