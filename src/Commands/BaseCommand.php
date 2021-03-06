<?php

namespace Juddling\RouteChecker\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class BaseCommand extends Command
{
    protected function getAllFilesInDir($directory, $fileExtension)
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

    /**
     * Given a file path, returns if this file should be parsed.
     *
     * @param $file
     * @return bool
     */
    protected function blacklisted($file): bool
    {
        return strpos($file, 'vendor/')
            || strpos($file, 'storage/framework/views/');
    }
}
