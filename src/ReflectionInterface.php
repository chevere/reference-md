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

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;

final class ReflectionInterface
{
    private ReflectionClass $reflectionClass;

    private bool $hasDocBlock = false;

    private DocBlock $docBlock;

    public function __construct(ReflectionClass $reflectionClass)
    {
        if(!$reflectionClass->isInterface()) {
            throw new InvalidArgumentException('Argument must be a reflection class for an interface');
        }
        // $interfaces = $reflectionClass->getInterfaces();
        // $key = array_key_first($interfaces);
        // $this->reflectionClass = $key !== null ? new ReflectionClass($key) : $reflectionClass;
        $this->reflectionClass = $reflectionClass;
        $factory = DocBlockFactory::createInstance();
        $docComment = $this->reflectionClass->getDocComment();
        if ($docComment != '') {
            $this->docBlock = $factory->create($docComment);
            $this->hasDocBlock = true;
        }
    }

    public function reflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
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
