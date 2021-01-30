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
use ReflectionParameter;

final class ParameterWriter
{
    private ReflectionParameter $reflection;

    private Reference $reference;

    public function __construct(ReflectionParameter $reflection)
    {
        $this->reflection = $reflection;
        $this->reference = new Reference(
            $this->reflection->hasType()
            ? $this->reflection->getType()->getName()
            : ''
        );
    }

    public function write(ReferenceHighlight $referenceHighlight, WriterInterface $writer): void
    {
        $writer->write(
            '*'
            . ($this->reflection->isVariadic() ? '...' : '')
            . $this->reflection->getName()
            . '*: '
            . $referenceHighlight->getHighlightTo($this->reference)
        );
    }
}
