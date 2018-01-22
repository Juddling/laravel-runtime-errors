<?php

namespace Juddling\RouteChecker;

use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\OutputInterface;

class FindInvalidRouteCalls
{
    protected $parser;
    /** @var Collection */
    protected $routeNames;
    /** @var Collection */
    protected $routeCalls;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->routeCalls = new Collection;
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

    public function findRouteFunctionCalls($file)
    {
        $code = file_get_contents($file);

        $traverser = new NodeTraverser;
        $visitor = new FindingVisitor(function (Node $node) use ($file) {
            if ($node instanceof Node\Expr\FuncCall) {
                if ($node->name instanceof Node\Name && $node->name->toString() === 'route') {
                    $firstArgument = $node->args[0];

                    if ($firstArgument->value instanceof \PhpParser\Node\Scalar\String_) {
                        // value of first argument is route name
                        $stringScalar = $firstArgument->value;
                        $routeName = $stringScalar->value;

                        $this->routeCalls->push([
                            'name' => $routeName,
                            'valid' => $this->routeExists($routeName),
                            'file' => $file
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

    public function renderTable(OutputInterface $output)
    {
        $table = new \Symfony\Component\Console\Helper\Table($output);
        $table->setHeaders(['Route Name', 'Valid', 'File']);
        $table->addRows($this->routeCalls->sortBy('valid')->map(function ($call) {
            $call['valid'] = $call['valid'] ? '✅' : '❌';
            return $call;
        })->toArray());
        $table->setStyle('borderless');
        $table->render();
    }
}
