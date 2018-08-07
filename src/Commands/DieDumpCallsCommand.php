<?php

namespace Juddling\RouteChecker\Commands;

use Juddling\RouteChecker\FindDieDumpCalls;

class DieDumpCallsCommand extends BaseCommand
{
    protected $signature = 'runtime-errors:dd';
    protected $description = 'Flags all calls to the dd() function';
    protected $functionCalls;

    public function __construct(FindDieDumpCalls $functionCalls)
    {
        $this->functionCalls = $functionCalls;

        parent::__construct();
    }

    public function handle()
    {
        foreach ($this->getAllFilesInDir(getcwd(), 'php') as $file) {
            if (!$this->blacklisted($file)) {
                $this->functionCalls->findFunctionCalls($file);
            }
        }

        $this->functionCalls->renderTable($this->getOutput());
    }
}
