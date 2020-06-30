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
use Go\ParserReflection\ReflectionMethod;
use Go\ParserReflection\ReflectionParameter;
use phpDocumentor\Reflection\DocBlockFactory;

final class MethodWriter
{
    private ReflectionMethod $reflection;

    private DocBlockFactory $factory;

    public function __construct(ReflectionMethod $reflection, DocBlockFactory $factory)
    {
        $this->reflection = $reflection;
        $this->factory = $factory;
    }

    public function write(ReferenceHighlight $referenceHighlight, WriterInterface $writer): void
    {
        $isConstruct = $this->reflection->getName() === '__construct';
        $writer->write('### ' . $this->reflection->getName() . '()' . PHP_EOL . PHP_EOL);
        $docComment = $this->reflection->getDocComment();
        if ($docComment !== false) {
            $docBlock = $this->factory->create((string) $docComment);
            $summary = $docBlock->getSummary();
            if ($summary !== '') {
                $writer->write(
                    '> ' . $summary . PHP_EOL . PHP_EOL
                );
            }
            $description = (string) $docBlock->getDescription();
            if ($description !== '') {
                $writer->write($description . PHP_EOL . PHP_EOL);
            }
        }
        /**
         * @var ReflectionParameter[] $parameters
         */
        $parameters = $this->reflection->getParameters();
        if ($this->reflection->getNumberOfParameters() > 0) {
            $writer->write('#### Parameters' . PHP_EOL . PHP_EOL);
            foreach ($parameters as $parameter) {
                $parameterWriter = new ParameterWriter($parameter);
                $parameterWriter->write($referenceHighlight, $writer);
            }
            if (!$isConstruct) {
                $writer->write(PHP_EOL);
            }
        }
        if (!$isConstruct) {
            $writer->write('#### Return' . PHP_EOL . PHP_EOL);
            $return = (string) $this->reflection->getReturnType();
            if ($return === '') {
                $return = 'void';
            } else {
                $return = $referenceHighlight
                    ->getHighlightTo(new Reference($return));
            }
            $writer->write($return . PHP_EOL);
        }
    }
}
