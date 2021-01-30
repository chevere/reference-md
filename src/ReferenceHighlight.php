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
use ReflectionClass;
use Throwable;

final class ReferenceHighlight
{
    public const PHP_URL_MANUAL = 'https://www.php.net/manual/';

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
            if (! $toStrBol->startsWith($try)) {
                array_pop($pops);
                $levels++;
            } else {
                $return = $toStr
                    ->withReplaceFirst($try, str_repeat('../', $levels))
                    ->withReplaceLast(
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
        $link = $this->getLinkTo($targetReference);
        if ($targetReference->isLinked() === false) {
            try {
                $reflection = new ReflectionClass($targetReference->name());
                if (! $reflection->isUserDefined()) {
                    $link = $this->getPHPManualPage($targetReference->name());
                }
            } catch (Throwable $e) {
            }
        }
        $linkBool = new StrBool($link);
        if ($linkBool->startsWith('.') || $linkBool->startsWith('http')) {
            return '[' . $targetReference->shortName() . ']'
            . '(' . $link . ')';
        }

        return $link;
    }

    private function getPHPManualPage(string $className): string
    {
        $className = strtolower($className);
        $className = str_replace('_', '-', $className);

        return self::PHP_URL_MANUAL . 'class.' . $className;
    }
}
