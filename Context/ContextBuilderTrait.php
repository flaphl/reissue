<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Context;

/**
 * Trait providing common context builder functionality.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
trait ContextBuilderTrait
{
    private array $context = [];

    /**
     * {@inheritdoc}
     */
    public function withGroups(array $groups): static
    {
        $this->context['groups'] = $groups;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withMaxDepth(int $depth): static
    {
        $this->context['max_depth'] = $depth;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withSerializeNull(bool $serialize): static
    {
        $this->context['serialize_null'] = $serialize;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function with(string $key, mixed $value): static
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->context;
    }
}
