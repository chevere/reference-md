<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\ReferenceMd;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;

final class ReflectionInterface
{
    private ReflectionClass $interface;

    private bool $hasDocBlock = false;

    private DocBlock $docBlock;

    public function __construct(ReflectionClass $reflectionClass)
    {
        $interfaces = $reflectionClass->getInterfaces();
        $key = array_key_first($interfaces);
        $this->interface = $key !== null ? new ReflectionClass($key) : $reflectionClass;
        $factory = DocBlockFactory::createInstance();
        $docComment = $this->interface->getDocComment();
        if ($docComment != '') {
            $this->docBlock = $factory->create($docComment);
            $this->hasDocBlock = true;
        }
    }

    public function interface(): ReflectionClass
    {
        return $this->interface;
    }

    public function hasDocBlock(): bool
    {
        return $this->hasDocBlock;
    }

    public function docBlock(): DocBlock
    {
        return $this->docBlock;
    }
}
