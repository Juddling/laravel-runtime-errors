<?php

namespace Juddling\RouteChecker\Commands;

use DirectoryIterator;
use Juddling\RouteChecker\FindInvalidRouteCalls;
use Juddling\RouteChecker\FindInvalidRouteDefinitions;
use RegexIterator;

class FindInvalidRouteDefinitionsCommand extends FindInvalidRouteCallsCommand
{
    protected $signature = 'runtime-errors:route-definitions';
    protected $description = 'Checks your route definitions to see if they point to an existing controller action';
    protected $routeDefinitions;

    public function __construct(FindInvalidRouteDefinitions $routeDefinitions)
    {
        $this->routeDefinitions = $routeDefinitions;

        parent::__construct(new FindInvalidRouteCalls());
    }

    public function handle()
    {
        foreach ($this->routeFiles() as $file) {
            $this->routeDefinitions->findFunctionCalls($file);
        }

        $this->routeDefinitions->renderTable($this->getOutput());
    }

    /**
     * Returns the path to all route files, e.g.:
     * routes/api.php
     * routes/web.php
     *
     * @return \Generator
     */
    protected function routeFiles()
    {
        $it = new DirectoryIterator(realpath(getcwd()) . '/routes/');
        $it = new RegexIterator($it, '(\.php$)');

        foreach ($it as $file) {
            /** @var \SplFileObject $file */
            $filepath = $file->getRealPath();
            yield $filepath;
        }
    }
}
