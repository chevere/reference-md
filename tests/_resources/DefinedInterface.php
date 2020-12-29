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

namespace Tests\_resources;

use Countable;

/**
 * This is a defined interface.
 *
 * This is the defined interface description.
 */
interface DefinedInterface
{
    /**
     * A test constant
     */
    public const TEST = 'test';

    /**
     * Construct summary.
     *
     * Construct description.
     *
     * @param string $argument Value to pass.
     * @param Countable $countable To count.
     */
    public function __construct(string $argument, Countable $countable);

    /**
     * Test summary.
     *
     * Test description.
     */
    public function test(): int;
}
