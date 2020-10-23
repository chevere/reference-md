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

use Chevere\ReferenceMd\Reference;
use Chevere\ReferenceMd\ReferenceHighlight;
use PHPUnit\Framework\TestCase;

final class ReferenceHighlightTest extends TestCase
{
    public function testSameReference(): void
    {
        $fromReference = new Reference('Chevere\\Interfaces\\Message\\MessageInterface');
        $toReference = new Reference('Chevere\\Interfaces\\Message\\MessageInterface');
        $this->assertSame(
            $fromReference->shortName(),
            (new ReferenceHighlight($fromReference))->getLinkTo($toReference)
        );
    }

    public function testSameFolder(): void
    {
        $fromReference = new Reference('Chevere\\Interfaces\\Message\\Ref1');
        $toReference = new Reference('Chevere\\Interfaces\\Message\\Ref2');
        $this->assertSame(
            './' . $toReference->markdownName(),
            (new ReferenceHighlight($fromReference))->getLinkTo($toReference)
        );
    }

    public function testLinkToLevelUp(): void
    {
        $fromReference = new Reference('Chevere\\Interfaces\\Controller\\ControllerInterface');
        $toReference = new Reference('Chevere\\Interfaces\\Message\\MessageInterface');
        $this->assertSame(
            '../Message/' . $toReference->markdownName(),
            (new ReferenceHighlight($fromReference))->getLinkTo($toReference)
        );
    }

    public function testLinkTwoLevelsUp(): void
    {
        $fromReference = new Reference('Chevere\\Interfaces\\Controller\\ControllerInterface');
        $toReference = new Reference('Chevere\\Exceptions\\Message\\MessageException');
        $this->assertSame(
            '../../Exceptions/Message/' . $toReference->markdownName(),
            (new ReferenceHighlight($fromReference))->getLinkTo($toReference)
        );
    }

    public function testLinkSeveralLevelsUp(): void
    {
        $fromReference = new Reference('Chevere\\Interfaces\\Plugin\\Plugs\\HooksInterface');
        $toReference = new Reference('Chevere\\Exceptions\\Message\\MessageException');
        $this->assertSame(
            '../../../Exceptions/Message/' . $toReference->markdownName(),
            (new ReferenceHighlight($fromReference))->getLinkTo($toReference)
        );
    }
}
