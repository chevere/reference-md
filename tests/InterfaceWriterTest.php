<?php

use Chevere\Components\Writer\StreamWriter;
use Chevere\ReferenceMd\InterfaceWriter;
use Chevere\ReferenceMd\ReflectionInterface;
use PHPUnit\Framework\TestCase;
use Tests\_resources\DefinedInterface;
use Tests\_resources\EmptyInterface;
use Tests\_resources\ExtendsInterface;

use function Chevere\Components\Writer\streamForString;

final class InterfaceWriterTest extends TestCase
{
    public function testEmptyInterface(): void
    {
        $url = 'https://sourcelink/';
        $reflectionInterface = new ReflectionInterface(
            new ReflectionClass(EmptyInterface::class)
        );
        $writer = new StreamWriter(streamForString(''));
        $interfaceWriter = new InterfaceWriter($url, $reflectionInterface, $writer);
        $interfaceWriter->write();
        $this->assertStringEqualsFile(
            __DIR__ . '/_resources/EmptyInterface.md',
            $writer->toString()
        );
    }

    public function testExtendsInterface(): void
    {
        $url = 'https://sourcelink/';
        $reflectionInterface = new ReflectionInterface(
            new ReflectionClass(ExtendsInterface::class)
        );
        $writer = new StreamWriter(streamForString(''));
        $interfaceWriter = new InterfaceWriter($url, $reflectionInterface, $writer);
        $interfaceWriter->write();
        $this->assertStringEqualsFile(
            __DIR__ . '/_resources/ExtendsInterface.md',
            $writer->toString()
        );
    }

    public function testDefinedInterface(): void
    {
        $url = 'https://sourcelink/';
        $reflectionInterface = new ReflectionInterface(
            new ReflectionClass(DefinedInterface::class)
        );
        $writer = new StreamWriter(streamForString(''));
        $interfaceWriter = new InterfaceWriter($url, $reflectionInterface, $writer);
        $interfaceWriter->write();
        $this->assertStringEqualsFile(
            __DIR__ . '/_resources/DefinedInterface.md',
            $writer->toString()
        );
    }
}