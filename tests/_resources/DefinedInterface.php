<?php

namespace Tests\_resources;

use Countable;

/**
 * This is a defined interface.
 * 
 * This is the defined interface description.
 */
interface DefinedInterface
{
    /** A test constant */
    const TEST = 'test';

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