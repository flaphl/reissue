<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\NameRecast;

/**
 * Converts property names between camelCase and snake_case.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
class CamelCaseToSnakeCaseNameConverter implements NameConverterInterface
{
    public function __construct(
        private readonly ?array $attributes = null,
        private readonly bool $lowerCamelCase = true
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(string $propertyName): string
    {
        if (null !== $this->attributes && !isset($this->attributes[$propertyName])) {
            return $propertyName;
        }

        return $this->camelCaseToSnakeCase($propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(string $propertyName): string
    {
        $camelCaseName = $this->snakeCaseToCamelCase($propertyName);

        if (null === $this->attributes || isset($this->attributes[$camelCaseName])) {
            return $camelCaseName;
        }

        return $propertyName;
    }

    /**
     * Converts camelCase to snake_case.
     */
    private function camelCaseToSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($input)));
    }

    /**
     * Converts snake_case to camelCase.
     */
    private function snakeCaseToCamelCase(string $input): string
    {
        $camelCase = str_replace('_', '', ucwords($input, '_'));

        if ($this->lowerCamelCase) {
            $camelCase = lcfirst($camelCase);
        }

        return $camelCase;
    }
}
