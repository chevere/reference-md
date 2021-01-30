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

use Chevere\Interfaces\Writer\WriterInterface;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;

final class InterfaceWriter
{
    private string $sourceUrl;

    private ReflectionInterface $reflectionInterface;

    private ReflectionClass $reflectionClass;

    private DocBlockFactory $docsFactory;

    private ReferenceHighlight $referenceHighligh;

    public function __construct(string $sourceUrl, ReflectionInterface $reflectionInterface, WriterInterface $writer)
    {
        $this->sourceUrl = $sourceUrl;
        $this->reflectionInterface = $reflectionInterface;
        $this->reflectionClass = $reflectionInterface->reflectionClass();
        $this->docsFactory = DocBlockFactory::createInstance();
        $this->writer = $writer;
        $this->referenceHighligh = new ReferenceHighlight(
            new Reference($this->reflectionClass->getName())
        );
    }

    public function write(): void
    {
        $this->writer->write(
            '---' .
            "\neditLink: false" .
            "\n---" .
            "\n\n# " . $this->reflectionClass->getShortName() .
            "\n\n`" . $this->reflectionClass->getName() . '`' .
            "\n\n[view source]({$this->sourceUrl})" .
            "\n"
        );
        if (! $this->reflectionClass->isInterface()) {
            $this->writeImplements();
        }
        $this->writeExtends();
        $this->writeDocBlock();
        $this->writeConstants();
        $this->writeMethods();
    }

    private function writeImplements(): void
    {
        $implements = $this->reflectionClass->getInterfaceNames();
        if ($implements !== []) {
            $this->writer->write("\n## Implements\n\n");
            /**
             * @var string $fqn
             */
            foreach ($implements as $fqn) {
                $this->writeListItemReference($fqn);
            }
        }
    }

    private function writeExtends(): void
    {
        if ($this->reflectionClass->isInterface()) {
            $extends = $this->reflectionClass->getInterfaceNames();
        } else {
            $parent = $this->reflectionClass->getParentClass();
            $extends = $parent !== false ? [$parent->getName()] : [];
        }
        if ($extends !== []) {
            $this->writer->write("\n## Extends\n\n");
            /**
             * @var string $extend
             */
            foreach ($extends as $extend) {
                $this->writeListItemReference($extend);
            }
        }
    }

    private function writeListItemReference(string $name): void
    {
        $this->writer->write(
            '- ' .
            $this->referenceHighligh->getHighlightTo(
                new Reference($name)
            ) .
            "\n"
        );
    }

    private function writeDocBlock(): void
    {
        if (! $this->reflectionInterface->hasDocBlock()) {
            return;
        }
        $this->writer->write(
            "\n## Description" .
            "\n\n" . $this->reflectionInterface->docBlock()->getSummary() .
            "\n"
        );
        $docDesc = (string) $this->reflectionInterface->docBlock()->getDescription();
        if ($docDesc !== '') {
            $this->writer->write("\n${docDesc}\n");
        }
    }

    private function writeConstants(): void
    {
        $constants = $this->reflectionClass->getConstants();
        if ($constants !== []) {
            $this->writer->write("\n## Constants\n");
            foreach ($constants as $const => $value) {
                $this->writer->write(
                    "\n### ${const}\n" .
                    "\nType `" . gettype($value) . '`' .
                    "\n\n```php" .
                    "\n" . var_export($value, true) .
                    "\n```\n"
                );
            }
        }
    }

    private function writeMethods(): void
    {
        $methods = $this->reflectionClass->getMethods();
        /**
         * @var ReflectionMethod[]
         */
        if ($methods !== []) {
            $wroteHeader = false;
            foreach ($methods as $method) {
                if (! $method->isUserDefined() || ! $method->isPublic()) {
                    continue;
                }
                if (! $wroteHeader) {
                    $this->writer->write("\n## Methods\n");
                    $wroteHeader = true;
                }
                $this->writer->write("\n### " . $method->getName() . "()\n");
                $methodWriter = new MethodWriter(
                    $method,
                    $this->docsFactory
                );
                $methodWriter->write(
                    new ReferenceHighlight(
                        new Reference(
                            $this->reflectionClass->getName()
                        )
                    ),
                    $this->writer
                );
                $this->writer->write("\n---\n");
            }
        }
    }
}
