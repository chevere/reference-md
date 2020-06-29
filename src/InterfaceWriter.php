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
use Chevere\ReferenceMd\ReflectionFileInterface;
use Go\ParserReflection\ReflectionMethod;
use phpDocumentor\Reflection\DocBlockFactory;

final class InterfaceWriter
{
    private string $sourceUrl;

    private ReflectionFileInterface $reflectionFile;

    private DocBlockFactory $docsFactory;

    public function __construct(string $sourceUrl, ReflectionFileInterface $reflectionFile)
    {
        $this->sourceUrl = $sourceUrl;
        $this->reflectionFile = $reflectionFile;
        $this->docsFactory = DocBlockFactory::createInstance();
    }

    public function write(WriterInterface $writer): void
    {
        $interface = $this->reflectionFile->interface();
        $writer->write(
            '`' . $interface->getNamespaceName() . '`' . PHP_EOL . PHP_EOL .
            '# ' . $interface->getShortName() . PHP_EOL . PHP_EOL .
            "[view source]($this->sourceUrl)" . PHP_EOL . PHP_EOL
        );
        $extends = $interface->getInterfaceNames();
        if ($extends !== []) {
            $writer->write('## Extends' . PHP_EOL . PHP_EOL);
            foreach ($extends as $extendFqn) {
                $writer->write('- [' . (new Reference($extendFqn))->getShortName() . ']()' . PHP_EOL);
            }
            $writer->write(PHP_EOL);
        }
        if ($this->reflectionFile->hasDocBlock()) {
            $writer->write(
                '## Description' . PHP_EOL . PHP_EOL .
                $this->reflectionFile->docBlock()->getSummary() . PHP_EOL . PHP_EOL
            );
            $docDesc = (string) $this->reflectionFile->docBlock()->getDescription();
            if ($docDesc !== '') {
                $writer->write($docDesc . PHP_EOL . PHP_EOL);
            }
        }
        /**
         * @var ReflectionClassConstant[]
         */
        $constants = $interface->getConstants();
        if ($constants !== []) {
            $writer->write(
                '## Constants' . PHP_EOL . PHP_EOL
            );
            foreach ($constants as $const => $value) {
                $writer->write(
                    '### ' . $const . PHP_EOL . PHP_EOL .
                    'Type `' . gettype($value) . '`' . PHP_EOL . PHP_EOL .
                    '```php' . PHP_EOL .
                    var_export($value, true) . PHP_EOL .
                    '```' . PHP_EOL . PHP_EOL
                );
            }
            $writer->write(PHP_EOL);
        }
        /**
         * @var ReflectionMethod[]
         */
        $methods = $interface->getMethods();
        if ($methods !== []) {
            $writer->write(
                '## Methods' . PHP_EOL .
                PHP_EOL . '---' . PHP_EOL .
                PHP_EOL
            );
            foreach ($methods as $method) {
                $methodWriter = new MethodWriter($method, $this->docsFactory);
                $methodWriter->write($writer);
            }
        }
    }
}
