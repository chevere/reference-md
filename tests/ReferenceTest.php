<?php

use Chevere\ReferenceMd\Reference;
use PHPUnit\Framework\TestCase;

final class ReferenceTest extends TestCase
{
    public function testExternalReference(): void
    {
        $name = 'name';
        $reference = new Reference($name);
        $this->assertSame($name, $reference->name());
        $this->assertSame($name, $reference->path());
        $this->assertSame($name, $reference->shortName());
        $this->assertSame("$name.md", $reference->markdownName());
        $this->assertSame("$name.md", $reference->markdownPath());
        $this->assertFalse($reference->isLinked());
        $this->assertSame('', $reference->base());
    }

    public function testInternalReference(): void
    {
        $ns = "Chevere\\";
        $shortName = 'Something';
        $fqn = $ns . $shortName;
        $path = str_replace('\\', '/', $fqn);
        $reference = new Reference($fqn);
        $this->assertSame($fqn, $reference->name());
        $this->assertSame($shortName, $reference->shortName());
        $this->assertSame($path, $reference->path());
        $this->assertSame("$shortName.md", $reference->markdownName());
        $this->assertSame("$path.md", $reference->markdownPath());
        $this->assertTrue($reference->isLinked());
        $this->assertSame($ns, $reference->base());
    }
}