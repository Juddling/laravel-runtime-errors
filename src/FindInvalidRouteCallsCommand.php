<?php

namespace Juddling\RouteChecker;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class FindInvalidRouteCallsCommand extends Command
{
    protected $signature = 'runtime-errors:route-calls';
    protected $description = 'Checks your route calls to see if they map to a registered named route';
    protected $routeCalls;

    public function __construct(FindInvalidRouteCalls $routeCalls)
    {
        $this->routeCalls = $routeCalls;

        parent::__construct();
    }

    private function getAllFilesInDir($directory, $fileExtension)
    {
        $directory = realpath($directory);
        $it = new RecursiveDirectoryIterator($directory);
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::LEAVES_ONLY);
        $it = new RegexIterator($it, '(\.' . preg_quote($fileExtension) . '$)');

        foreach ($it as $file) {
            /** @var \SplFileObject $file */
            $filepath = $file->getRealPath();
            yield $filepath;
        }
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

    protected function blacklisted($file)
    {
        return strpos($file, 'vendor/')
            || strpos($file, 'storage/framework/views/');
    }
}
