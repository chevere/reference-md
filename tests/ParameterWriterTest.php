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

use function Chevere\Components\Writer\streamTemp;
use Chevere\Components\Writer\StreamWriter;
use Chevere\ReferenceMd\ParameterWriter;
use Chevere\ReferenceMd\Reference;
use Chevere\ReferenceMd\ReferenceHighlight;
use PHPUnit\Framework\TestCase;

final class ParameterWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $anon = function (string $string) {
        };
        $reflection = new ReflectionParameter($anon, 'string');
        $parameterWriter = new ParameterWriter($reflection);
        $writer = new StreamWriter(streamTemp(''));
        $parameterWriter->write(
            new ReferenceHighlight(
                new Reference('test')
            ),
            $writer
        );
        $this->assertSame('*string*: string', $writer->toString());
    }

    public function testWriteSpread(): void
    {
        $anon = function (string ...$string) {
        };
        $reflection = new ReflectionParameter($anon, 'string');
        $parameterWriter = new ParameterWriter($reflection);
        $writer = new StreamWriter(streamTemp(''));
        $parameterWriter->write(
            new ReferenceHighlight(
                new Reference('test')
            ),
            $writer
        );
        $this->assertSame('*...string*: string', $writer->toString());
    }
}
