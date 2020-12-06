<?php

use Chevere\Components\Writer\StreamWriter;
use Chevere\ReferenceMd\ParameterWriter;
use Chevere\ReferenceMd\Reference;
use Chevere\ReferenceMd\ReferenceHighlight;
use PHPUnit\Framework\TestCase;

use function Chevere\Components\Writer\streamForString;

final class ParameterWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $anon = function(string $string) {};
        $reflection = new ReflectionParameter($anon, 'string');
        $parameterWriter = new ParameterWriter($reflection);
        $stream = streamForString('');
        $writer = new StreamWriter($stream);
        $parameterWriter->write(
            new ReferenceHighlight(
                new Reference('test')
            ),
            $writer
        );
        $this->assertSame('string `$string`', $writer->toString());
    }
}