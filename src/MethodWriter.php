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
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionMethod;
use ReflectionParameter;

final class MethodWriter
{
    private ReflectionMethod $reflection;

    private DocBlockFactory $docBlockFactory;

    private Context $context;

    private bool $isConstruct;

    private DocBlock $docBlock;

    public function __construct(ReflectionMethod $reflection, DocBlockFactory $factory)
    {
        $this->reflection = $reflection;
        $this->docBlockFactory = $factory;
        $this->context = (new ContextFactory())->createFromReflector($this->reflection);
        $this->isConstruct = $this->reflection->getName() === '__construct';
        $docComment = $this->reflection->getDocComment();
        if ($docComment !== false) {
            $this->docBlock = $this->docBlockFactory->create((string) $docComment, $this->context);
        }
    }

    public function write(ReferenceHighlight $referenceHighlight, WriterInterface $writer): void
    {
        $this->handleWriteSummary($writer);
        /** @var ReflectionParameter[] $parameters */

        $parameters = $this->reflection->getParameters();
        if ($this->reflection->getNumberOfParameters() > 0) {
            $writer->write("\n::: warning Parameters\n");
            $index = 1;
            foreach ($parameters as $parameter) {
                $writer->write('- ');
                $parameterWriter = new ParameterWriter($parameter);
                $parameterWriter->write($referenceHighlight, $writer);
                $index++;
                $writer->write("\n");
            }
            $writer->write(":::\n");
        }
        $this->handleWriteThrows($referenceHighlight, $writer);
        $this->handleWriteReturn($referenceHighlight, $writer);
        $this->handleWriteDescription($writer);
    }

    private function handleWriteSummary(WriterInterface $writer): void
    {
        if (isset($this->docBlock) && $this->docBlock->getSummary() !== '') {
            $writer->write("\n" . $this->docBlock->getSummary() . "\n");
        }
    }

    private function handleWriteThrows(ReferenceHighlight $referenceHighlight, WriterInterface $writer): void
    {
        if (isset($this->docBlock)) {
            /** @var Throws[] $throwTags */
            $throwTags = $this->docBlock->getTagsByName('throws');
            if ($throwTags !== []) {
                $writer->write("\n::: danger Throws\n");
                foreach ($throwTags as $throw) {
                    $throwType = ltrim($throw->getType()->__toString(), '\\');
                    $throwReference = new Reference($throwType);
                    if (! class_exists($throwType)) {
                        $writer->write(
                            '- ⚠ Unknown type `' . $throwReference->shortName() . "` declared in `@throws` tag`\n"
                        );
                    } else {
                        $description = $throw->getDescription();
                        $description = $description !== null
                            ? ' ' . ((string) $description) : '';
                        $writer->write(
                            '- ' . $referenceHighlight->getHighlightTo($throwReference) . $description . "\n"
                        );
                    }
                }
                $writer->write(":::\n");
            }
        }
    }

    private function handleWriteReturn(ReferenceHighlight $referenceHighlight, WriterInterface $writer): void
    {
        if ($this->isConstruct) {
            return;
        }
        $return = $this->reflection->hasReturnType()
            ? $this->reflection->getReturnType()->getName() : 'void';
        $return = $referenceHighlight
            ->getHighlightTo(new Reference($return));
        $writer->write(
            "\n::: tip Return\n" .
            $return . "\n" .
            ':::' . "\n"
        );
    }

    private function handleWriteDescription(WriterInterface $writer): void
    {
        if (isset($this->docBlock)) {
            $description = (string) $this->docBlock->getDescription();
            if ($description !== '') {
                $writer->write("\n${description}\n");
            }
        }
    }
}
