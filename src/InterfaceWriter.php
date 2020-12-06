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
use phpDocumentor\Reflection\DocBlockFactory;

final class InterfaceWriter
{
    private string $sourceUrl;

    private ReflectionInterface $reflectionInterface;

    private DocBlockFactory $docsFactory;

    public function __construct(string $sourceUrl, ReflectionInterface $reflectionInterface)
    {
        $this->sourceUrl = $sourceUrl;
        $this->reflectionInterface = $reflectionInterface;
        $this->docsFactory = DocBlockFactory::createInstance();
    }

    public function write(WriterInterface $writer): void
    {
        $interface = $this->reflectionInterface->interface();
        $writer->write(
            "---\n" .
            "editLink: false\n" .
            "---\n\n" .
            '# ' . $interface->getShortName() . "\n\n" .
            '`' . $interface->getName() . '`' . "\n\n" .
            "[view source]($this->sourceUrl)" . "\n"
        );
        $referenceHighligh = new ReferenceHighlight(
            new Reference($interface->getName())
        );
        $implements = $interface->getInterfaceNames();
        if ($implements !== []) {
            $writer->write("\n## Implements\n\n");
            foreach ($implements as $fqn) {
                $writer->write(
                    '- ' .
                    $referenceHighligh->getHighlightTo(new Reference($fqn)) . "\n"
                );
            }
        }
        if ($interface->getParentClass() !== false) {
            $extends = $interface->getParentClass()->getName();
        }
        if (isset($extends)) {
            $writer->write("\n## Extends\n\n");
            $writer->write(
                '- ' .
                $referenceHighligh->getHighlightTo(new Reference($extends)) . "\n"
            );
        }
        if ($this->reflectionInterface->hasDocBlock()) {
            $writer->write(
                "\n## Description\n" .
                "\n" . $this->reflectionInterface->docBlock()->getSummary() . "\n"
            );
            $docDesc = (string) $this->reflectionInterface->docBlock()->getDescription();
            if ($docDesc !== '') {
                $writer->write("\n$docDesc\n");
            }
        }
        /**
         * @var ReflectionClassConstant[]
         */
        $constants = $interface->getConstants();
        if ($constants !== []) {
            $writer->write("\n## Constants\n");
            foreach ($constants as $const => $value) {
                $writer->write(
                    "\n### $const\n\n" .
                    'Type `' . gettype($value) . "`\n\n" .
                    "```php\n" .
                    var_export($value, true) . "\n" .
                    "```\n"
                );
            }
        }
        /**
         * @var ReflectionMethod[]
         */
        $methods = $interface->getMethods();
        if ($methods !== []) {
            $writer->write(
                "\n## Methods\n"
            );
            foreach ($methods as $method) {
                if (!$method->isUserDefined()) {
                    continue;
                }
                $writer->write("\n### " . $method->getName() . "()\n");
                $methodWriter = new MethodWriter(
                    $method,
                    $this->docsFactory
                );
                $methodWriter->write(
                    new ReferenceHighlight(
                        new Reference($interface->getName())
                    ),
                    $writer
                );
                $writer->write(
                    "\n---\n"
                );
            }
        }
    }
}
