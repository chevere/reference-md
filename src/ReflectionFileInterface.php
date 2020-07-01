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

use Go\ParserReflection\ReflectionClass;
use Go\ParserReflection\ReflectionFile;
use Go\ParserReflection\ReflectionFileNamespace;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;

final class ReflectionFileInterface
{
    private ReflectionClass $interface;

    private bool $hasDocBlock = false;

    private DocBlock $docBlock;

    public function __construct(ReflectionFile $reflectionFile)
    {
        $reflection = $reflectionFile;
        /**
         * @var ReflectionFileNamespace[]
         */
        $namespaces = $reflection->getFileNamespaces();
        /**
         * @var string $ns
         */
        $ns = array_key_first($namespaces);
        $fileNs = $namespaces[$ns];
        /**
         * @var ReflectionClass[]
         */
        $interfaces = $fileNs->getClasses();
        $key = array_key_first($interfaces);
        $this->interface = $interfaces[$key];
        $factory = DocBlockFactory::createInstance();
        $docComment = $this->interface->getDocComment();
        if ($docComment !== false) {
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
