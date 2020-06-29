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

use Chevere\Components\Str\StrBool;

final class Reference
{
    private string $name = '';

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->isLinked = (new StrBool($name))
            ->startsWith('Chevere\\');
    }

    public function getShortName(): string
    {
        if ($this->isLinked === false) {
            return $this->name;
        }
        $explode = explode('\\', $this->name);

        return $explode[array_key_last($explode)];
    }

    public function getHighligh(): string
    {
        if ($this->isLinked === false) {
            return $this->name;
        }

        return strtr('[%type%](%link%)', [
            '%type%' => $this->getShortName(),
            '%link%' => $this->getUrl(),
        ]);
    }

    public function getUrl(): string
    {
        $slashes = str_replace('\\', '/', $this->name);

        return './references/' . $slashes . '.md';
    }

    public function isLinked(): bool
    {
        return $this->isLinked;
    }
}
