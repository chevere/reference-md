<?php

use Chevere\Components\Writer\StreamWriter;
use Chevere\Interfaces\Writer\WriterInterface;
use Chevere\ReferenceMd\InterfaceWriter;
use Chevere\ReferenceMd\ReflectionInterface;
use PHPUnit\Framework\TestCase;
use Tests\_resources\DefinedInterface;
use Tests\_resources\EmptyInterface;
use Tests\_resources\ExtendsInterface;
use Tests\_resources\ImplementsClass;

use function Chevere\Components\Writer\streamForString;

final class InterfaceWriterTest extends TestCase
{
    private function getInterfaceWriter(string $className, WriterInterface $writer): InterfaceWriter {
        return new InterfaceWriter(
            './',
            new ReflectionInterface(
                new ReflectionClass($className)
            ),
            $writer);
    }
    public function testEmptyInterface(): void
    {
        $writer = new StreamWriter(streamForString(''));
        $interfaceWriter = $this->getInterfaceWriter(
            EmptyInterface::class,
            $writer
        );
        $interfaceWriter->write();
        $this->assertStringEqualsFile(
            __DIR__ . '/_resources/EmptyInterface.md',
            $writer->toString()
        );
    }

    public function testExtendsInterface(): void
    {
        $writer = new StreamWriter(streamForString(''));
        $interfaceWriter = $this->getInterfaceWriter(
            ExtendsInterface::class,
            $writer
        );
        $interfaceWriter->write();
        $this->assertStringEqualsFile(
            __DIR__ . '/_resources/ExtendsInterface.md',
            $writer->toString()
        );
    }

    public function testDefinedInterface(): void
    {
        $writer = new StreamWriter(streamForString(''));
        $interfaceWriter = $this->getInterfaceWriter(
            DefinedInterface::class,
            $writer
        );
        $interfaceWriter->write();
        $this->assertStringEqualsFile(
            __DIR__ . '/_resources/DefinedInterface.md',
            $writer->toString()
        );
    }

    public function testImplementsClass(): void
    {
        $writer = new StreamWriter(streamForString(''));
        $interfaceWriter = $this->getInterfaceWriter(
            ImplementsClass::class,
            $writer
        );
        $interfaceWriter->write();
        $this->assertStringEqualsFile(
            __DIR__ . '/_resources/ImplementsClass.md',
            $writer->toString()
        );
    }
}