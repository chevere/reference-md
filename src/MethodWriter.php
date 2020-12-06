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
use Go\ParserReflection\ReflectionParameter;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionMethod;

final class MethodWriter
{
    private ReflectionMethod $reflection;

    private DocBlockFactory $factory;

    public function __construct(ReflectionMethod $reflection, DocBlockFactory $factory)
    {
        $this->reflection = $reflection;
        $this->factory = $factory;
    }

    public function write(Reference $reference, WriterInterface $writer): void
    {
        $contextFactory = new ContextFactory();
        $context = $contextFactory->createFromReflector($this->reflection);
        $referenceHighlight = new ReferenceHighlight($reference);
        $isConstruct = $this->reflection->getName() === '__construct';
        $docComment = $this->reflection->getDocComment();
        if ($docComment !== false) {
            $docBlock = $this->factory->create((string) $docComment, $context);
            $summary = $docBlock->getSummary();
            if ($summary !== '') {
                $writer->write("\n$summary\n");
            }
        }
        /**
         * @var ReflectionParameter[] $parameters
         */
        $parameters = $this->reflection->getParameters();
        if ($this->reflection->getNumberOfParameters() > 0) {
            $writer->write("\n#### Parameters\n");
            $index = 0;
            foreach ($parameters as $parameter) {
                $index++;
                $writer->write("\n$index. ");
                $parameterWriter = new ParameterWriter($parameter);
                $parameterWriter->write($referenceHighlight, $writer);
            }
            $writer->write(PHP_EOL);
        }
        if (isset($docBlock)) {
            /**
             * @var Throws[] $throwTags
             */
            $throwTags = $docBlock->getTagsByName('throws');
            if ($throwTags !== []) {
                $writer->write("\n::: danger THROWS\n");
                foreach ($throwTags as $throw) {
                    $throwType = ltrim($throw->getType()->__toString(), '\\');
                    $throwReference = new Reference($throwType);
                    if (!class_exists($throwType)) {
                        $writer->write(
                            '- ⚠ Unknown type `' . $throwReference->shortName() . "` declared in `@throws` tag`\n"
                        );
                    } else {
                        $writer->write(
                            '- ' . $referenceHighlight->getHighlightTo($throwReference) . "\n"
                        );
                        if ($throw->getDescription() !== null && $throw->getDescription()->__toString() !== '') {
                            $writer->write($throw->getDescription()->__toString() . "\n");
                        }
                    }
                }
                $writer->write(":::\n");
            }
        }
        if (!$isConstruct) {
            $return = $this->reflection->hasReturnType()
                ? $this->reflection->getReturnType()->getName()
                : '';
            if ($return === '') {
                $return = 'void';
            } else {
                $return = $referenceHighlight
                    ->getHighlightTo(new Reference($return));
            }
            $writer->write(
                "\n::: tip RETURN\n" .
                $return . "\n" .
                ':::' . "\n"
            );
        }
        if (isset($docBlock)) {
            $description = (string) $docBlock->getDescription();
            if ($description !== '') {
                $writer->write("\n$description\n");
            }
        }
    }
}
