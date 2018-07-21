<?php

namespace Juddling\RouteChecker;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class FindInvalidViewCallsCommand extends Command
{
    protected $signature = 'juddling:find-invalid-views';
    protected $description = 'Checks your view calls to see if they map to a file that exists.';
    protected $routeCalls;

    public function __construct(FindInvalidViewCalls $views)
    {
        $this->views = $views;

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
                $this->views->findFunctionCalls($file);
            }
        }

        $this->views->renderTable($this->getOutput());
    }

    protected function blacklisted($file)
    {
        return strpos($file, 'vendor/')
            || strpos($file, 'storage/framework/views/');
    }
}
