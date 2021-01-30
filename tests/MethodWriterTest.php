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

use function Chevere\Components\Writer\streamForString;
use Chevere\Components\Writer\StreamWriter;
use Chevere\Interfaces\Writer\WriterInterface;
use Chevere\ReferenceMd\MethodWriter;
use Chevere\ReferenceMd\Reference;
use Chevere\ReferenceMd\ReferenceHighlight;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPUnit\Framework\TestCase;

final class MethodWriterTest extends TestCase
{
    public function getWriterForReflection(ReflectionMethod $reflection): WriterInterface
    {
        $methodWriter = new MethodWriter($reflection, DocBlockFactory::createInstance());
        $stream = streamForString('');
        $writer = new StreamWriter($stream);
        $methodWriter->write(
            new ReferenceHighlight(
                new Reference('test')
            ),
            $writer
        );

        return $writer;
    }

    public function testNoDocsNoParametersNoReturnWrite(): void
    {
        $class = new class() {
            public function __construct()
            {
            }
        };
        $writer = $this->getWriterForReflection(
            new ReflectionMethod($class, '__construct')
        );
        $this->assertSame('', $writer->toString());
    }

    public function testSummary(): void
    {
        $class = new class() {
            /**
             * Summary for this method.
             */
            public function method()
            {
            }
        };
        $writer = $this->getWriterForReflection(
            new ReflectionMethod($class, 'method')
        );
        $this->assertSame(
            "\nSummary for this method.\n" .
            "\n::: tip RETURN" .
            "\nvoid" .
            "\n:::\n",
            $writer->toString()
        );
    }

    public function testDescription(): void
    {
        $class = new class() {
            /**
             * Summary for this method.
             *
             * Description for what it does.
             */
            public function method()
            {
            }
        };
        $writer = $this->getWriterForReflection(
            new ReflectionMethod($class, 'method')
        );
        $this->assertSame(
            "\nSummary for this method.\n" .
            "\n::: tip RETURN" .
            "\nvoid" .
            "\n:::\n" .
            "\nDescription for what it does.\n",
            $writer->toString()
        );
    }

    public function testReturn(): void
    {
        $class = new class() {
            public function method(): bool
            {
                return true;
            }
        };
        $writer = $this->getWriterForReflection(
            new ReflectionMethod($class, 'method')
        );
        $this->assertSame(
            "\n::: tip RETURN" .
            "\nbool" .
            "\n:::\n",
            $writer->toString()
        );
    }

    public function testParameter(): void
    {
        $class = new class() {
            public function method(object $parameter)
            {
            }
        };
        $writer = $this->getWriterForReflection(
            new ReflectionMethod($class, 'method')
        );
        $this->assertSame(
            "\n**Parameters:**\n" .
            "\n- parameter: object" .
            "\n\n::: tip RETURN" .
            "\nvoid" .
            "\n:::\n",
            $writer->toString()
        );
    }

    public function testThrows(): void
    {
        $class = new class() {
            /**
             * @throws \Exception On some error.
             * @throws NotFoundException
             */
            public function method()
            {
            }
        };
        $writer = $this->getWriterForReflection(
            new ReflectionMethod($class, 'method')
        );
        $this->assertSame(
            "\n::: danger THROWS" .
            "\n- [Exception](https://www.php.net/manual/class.exception) On some error." .
            "\n- ⚠ Unknown type `NotFoundException` declared in `@throws` tag`" .
            "\n:::" .
            "\n\n::: tip RETURN" .
            "\nvoid" .
            "\n:::\n",
            $writer->toString()
        );
    }
}
