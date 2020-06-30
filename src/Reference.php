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

final class Reference
{
    private string $name;

    // private ReferenceHighlight $highlight;

    private bool $isLinked;

    private string $shortName;

    private string $base = '';

    private string $path;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->isLinked = (new StrBool($name))
            ->startsWith('Chevere\\');
        $this->setShortName();
        if ($this->isLinked) {
            $this->base = (new Str($this->name))->replaceLast($this->shortName, '')->toString();
        }
        $this->path = str_replace('\\', '/', $this->name);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function shortName(): string
    {
        return $this->shortName;
    }

    public function markdownName(): string
    {
        return $this->shortName . '.md';
    }

    public function isLinked(): bool
    {
        return $this->isLinked;
    }

    public function base(): string
    {
        return $this->base;
    }

    public function path(): string
    {
        return $this->path;
    }

    private function setShortName(): void
    {
        if ($this->isLinked === false) {
            $this->shortName = $this->name;

            return;
        }
        $explode = explode('\\', $this->name);

        $this->shortName = $explode[array_key_last($explode)];
    }
}
