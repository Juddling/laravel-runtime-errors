<?php

namespace Juddling\RouteChecker;

class FindDieDumpCalls extends FindInvalid
{
    protected function getFunctionName()
    {
        return 'dd';
    }

    protected function check(string $argument): bool
    {
        return false;
    }
}
