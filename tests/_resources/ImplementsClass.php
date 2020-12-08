<?php

namespace Tests\_resources;

use Countable;

class ImplementsClass implements Countable
{
    public function count(): int
    {
        return 0;
    }
}