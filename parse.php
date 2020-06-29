<?php


declare(strict_types=1);

use Chevere\Components\Writer\StreamWriterFromString;
use Go\ParserReflection\ReflectionClass;
use Go\ParserReflection\ReflectionClassConstant;
use Go\ParserReflection\ReflectionFile;
use Go\ParserReflection\ReflectionFileNamespace;
use Go\ParserReflection\ReflectionMethod;
use Go\ParserReflection\ReflectionParameter;
use phpDocumentor\Reflection\DocBlockFactory;

require 'vendor/autoload.php';

function getShortName(string $fqn): string
{
    $explode = explode('\\', $fqn);

    return $explode[array_key_last($explode)];
}

$remote = 'https://github.com/chevere/chevere/blob/master/';
$target = 'interfaces/ThrowableHandler/ThrowableHandlerInterface.php';
$urlMap = $remote . $target;
$writer = new StreamWriterFromString(__DIR__ . '/md.md', 'w');
$reflection = new ReflectionFile('vendor/chevere/chevere/' . $target);
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
$interface = $interfaces[$key];
$factory = DocBlockFactory::createInstance();
// Title + FQN
$writer->write(
    '`' . $interface->getNamespaceName() . '`' . PHP_EOL . PHP_EOL .
    '# ' . $interface->getShortName() . PHP_EOL . PHP_EOL .
    "[view source]($urlMap)" . PHP_EOL . PHP_EOL
);
$extends = $interface->getInterfaceNames();
if ($extends !== []) {
    $writer->write('## Extends' . PHP_EOL . PHP_EOL);
    foreach ($extends as $extendFqn) {
        $writer->write('- [' . getShortName($extendFqn) . ']()' . PHP_EOL);
    }
    $writer->write(PHP_EOL);
}
$docComment = $interface->getDocComment();
if ($docComment !== false) {
    $docBlock = $factory->create($docComment);
    // Description
    $writer->write(
        '## Description' . PHP_EOL . PHP_EOL .
        $docBlock->getSummary() . PHP_EOL . PHP_EOL .
        (string) $docBlock->getDescription() . PHP_EOL . PHP_EOL
    );
}
/**
 * @var ReflectionClassConstant[]
 */
$constants = $interface->getConstants();
if ($constants !== []) {
    // Constants
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
// Methods
$writer->write(
    '## Methods' . PHP_EOL .
    PHP_EOL . '---' . PHP_EOL .
    PHP_EOL
);
/**
 * @var ReflectionMethod[]
 */
$methods = $interface->getMethods();
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
            $writer->write('[' . getShortName((string) $parameter->getType()) . ']() `$' . $parameter->getName() . '`' . PHP_EOL . PHP_EOL);
        }
    }
    $writer->write('#### Return' . PHP_EOL . PHP_EOL);
    $return = getShortName((string) $method->getReturnType());
    if ($return === '') {
        $return = 'void';
    } else {
        $return = "[$return]()";
    }
    $writer->write(
        $return . PHP_EOL .
        PHP_EOL . '---' . PHP_EOL .
        PHP_EOL
    );
}
