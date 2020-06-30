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

use Chevere\Components\Str\Str;
use Chevere\Components\Str\StrBool;
use Chevere\ReferenceMd\Reference;

final class ReferenceHighlight
{
    private Reference $reference;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
        $this->explode = explode('/', $reference->path());
        if (count($this->explode) > 1) {
            array_pop($this->explode);
        }
    }

    public function getLinkTo(Reference $targetReference): string
    {
        if (
            $this->reference->name() === $targetReference->name()
            || $targetReference->isLinked() === false
        ) {
            return $targetReference->shortName();
        }
        if ($this->reference->base() === $targetReference->base()) {
            return './' . $targetReference->markdownName();
        }
        $return = $targetReference->name();
        $pops = $this->explode;
        $levels = 0;
        $toStr = new Str($targetReference->path());
        $toStrBol = new StrBool($targetReference->path());
        foreach ($pops as $chop) {
            $try = implode('/', $pops) . '/';
            if (!$toStrBol->startsWith($try)) {
                array_pop($pops);
                $levels++;
            } else {
                $return = $toStr
                    ->replaceFirst($try, str_repeat('../', $levels))
                    ->replaceLast(
                        $targetReference->shortName(),
                        $targetReference->markdownName()
                    )
                    ->toString();
                break;
            }
        }

        return $return;
    }

    public function getHighlightTo(Reference $targetReference): string
    {
        if ($targetReference->isLinked() === false) {
            return $targetReference->name();
        }
        $link = $this->getLinkTo($targetReference);
        if (strpos($link, '.', 1) !== false) {
            return '[' . $targetReference->shortName() . ']('
            . $link
            . ')';
        }

        return $targetReference->shortName();
    }
}
