<?php

namespace Juddling\RouteChecker\Commands;

use Juddling\RouteChecker\FindInvalidRouteCalls;

class FindInvalidRouteCallsCommand extends BaseCommand
{
    protected $signature = 'runtime-errors:route-calls';
    protected $description = 'Checks your route calls to see if they map to a registered named route';
    protected $routeCalls;

    public function __construct(FindInvalidRouteCalls $routeCalls)
    {
        $this->routeCalls = $routeCalls;

        parent::__construct();
    }

    public function handle()
    {
        foreach ($this->getAllFilesInDir(getcwd(), 'php') as $file) {
            if (!$this->blacklisted($file)) {
                $this->routeCalls->findFunctionCalls($file);
            }
        }

        $this->routeCalls->renderTable($this->getOutput());
    }
}
