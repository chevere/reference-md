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

use Chevere\Components\Writer\StreamWriterFromString;
use Chevere\ReferenceMd\Reference;
use Chevere\ReferenceMd\ReflectionFileInterface;
use Go\ParserReflection\ReflectionClassConstant;
use Go\ParserReflection\ReflectionMethod;
use Go\ParserReflection\ReflectionParameter;
use phpDocumentor\Reflection\DocBlockFactory;

require 'vendor/autoload.php';

$remote = 'https://github.com/chevere/chevere/blob/master/';
$target = 'interfaces/Str/StrInterface.php';
$urlMap = $remote . $target;
$writer = new StreamWriterFromString(__DIR__ . '/md.md', 'w');
$reflection = new ReflectionFileInterface('vendor/chevere/chevere/' . $target);
$interface = $reflection->interface();
$factory = DocBlockFactory::createInstance();
$writer->write(
    '`' . $interface->getNamespaceName() . '`' . PHP_EOL . PHP_EOL .
    '# ' . $interface->getShortName() . PHP_EOL . PHP_EOL .
    "[view source]($urlMap)" . PHP_EOL . PHP_EOL
);
$extends = $interface->getInterfaceNames();
if ($extends !== []) {
    $writer->write('## Extends' . PHP_EOL . PHP_EOL);
    foreach ($extends as $extendFqn) {
        $writer->write('- [' . (new Reference($extendFqn))->getShortName() . ']()' . PHP_EOL);
    }
    $writer->write(PHP_EOL);
}
if ($reflection->hasDocBlock()) {
    $writer->write(
        '## Description' . PHP_EOL . PHP_EOL .
        $reflection->docBlock()->getSummary() . PHP_EOL . PHP_EOL .
        (string) $reflection->docBlock()->getDescription() . PHP_EOL . PHP_EOL
    );
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
        $writer->write('### ' . $method->getName() . '()' . PHP_EOL . PHP_EOL);
        $docComment = $method->getDocComment();
        if ($docComment !== false) {
            $docBlock = $factory->create((string) $docComment);
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
        $parameters = $method->getParameters();
        if (count($parameters) > 0) {
            $writer->write('#### Parameters' . PHP_EOL . PHP_EOL);
            foreach ($parameters as $pos => $parameter) {
                $type = $parameter->getType();
                $writer->write('- ' . (new Reference((string) $type))->getHighligh() . ' `$' . $parameter->getName() . '`' . PHP_EOL);
            }
            $writer->write(PHP_EOL);
        }
        if ($method->getName() !== '__construct') {
            $writer->write('#### Return' . PHP_EOL . PHP_EOL);
            $return = (string) $method->getReturnType();
            if ($return === '') {
                $return = 'void';
            } else {
                $return = (new Reference($return))->getHighligh();
            }
            $writer->write($return . PHP_EOL . PHP_EOL);
        }
        $writer->write(
            '---' . PHP_EOL . PHP_EOL
        );
    }
}
