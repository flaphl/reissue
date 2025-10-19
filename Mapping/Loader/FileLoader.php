<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Mapping\Loader;

use Flaphl\Element\Reissue\Exception\InvalidArgumentException;

/**
 * Base class for file-based metadata loaders.
 *
 * @author Jade Phyressi <jade@flaphl.com>
 */
abstract class FileLoader implements LoaderInterface
{
    /**
     * @var string The path to the mapping file
     */
    protected string $file;

    /**
     * @param string $file The path to the mapping file
     * @throws InvalidArgumentException If the file does not exist
     */
    public function __construct(string $file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('Mapping file "%s" does not exist.', $file));
        }

        if (!is_readable($file)) {
            throw new InvalidArgumentException(sprintf('Mapping file "%s" is not readable.', $file));
        }

        $this->file = $file;
    }

    /**
     * Gets the file path.
     */
    public function getFile(): string
    {
        return $this->file;
    }
}
