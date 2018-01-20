<?php

namespace Juddling\RouteChecker;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class FindInvalidRouteCalls extends Command
{
    protected $signature = 'juddling:find-invalid-routes';
    protected $description = 'Checks your route calls to see if they map to a registered named route';
    protected $parser;
    /** @var Collection */
    protected $routeNames;
    protected $table;
    protected $routeCalls;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->routeCalls = new Collection;

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

    protected function routeExists($routeName): bool
    {
        if ($this->routeNames) {
            return $this->routeNames->contains($routeName);
        }

        // set the routes
        $this->routeNames = collect(\Route::getRoutes())->map(function (Route $route) {
            return $route->getName();
        })->filter();

        // now try this function again
        return $this->routeExists($routeName);
    }

    public function handle()
    {
        foreach ($this->getAllFilesInDir(getcwd(), 'php') as $file) {
            if (!$this->blacklisted($file)) {
                $this->parseFile($file);
            }
        }

        $this->renderTable();
    }

    protected function blacklisted($file)
    {
        return strpos($file, 'vendor/');
    }

    protected function parseFile($file)
    {
        $this->findRouteFunctionCalls(file_get_contents($file));
    }

    protected function findRouteFunctionCalls($code)
    {
        $traverser = new NodeTraverser;
        $visitor = new FindingVisitor(function (Node $node) {
            if ($node instanceof Node\Expr\FuncCall) {
                if ($node->name instanceof Node\Name && $node->name->toString() === 'route') {
                    $firstArgument = $node->args[0];

                    if ($firstArgument->value instanceof \PhpParser\Node\Scalar\String_) {
                        // value of first argument is route name
                        $stringScalar = $firstArgument->value;
                        $routeName = $stringScalar->value;

                        $this->routeCalls->push([
                            'name' => $routeName,
                            'valid' => $this->routeExists($routeName)
                        ]);

                        return true;
                    }
                }
            }

            return false;
        });
        $traverser->addVisitor($visitor);
        $traverser->traverse($this->parser->parse($code));
        return $visitor->getFoundNodes();
    }

    private function renderTable()
    {
        $this->table = new \Symfony\Component\Console\Helper\Table($this->getOutput());
        $this->table->setHeaders(['Route Name', 'Valid']);
        $this->table->addRows($this->routeCalls->sortBy('valid')->map(function ($call) {
            $call['valid'] = $call['valid'] ? '✅' : '❌';
            return $call;
        })->toArray());
        $this->table->setStyle('borderless');
        $this->table->render();
    }
}
