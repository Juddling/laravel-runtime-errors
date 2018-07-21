<?php

namespace Juddling\RouteChecker;

use PhpParser\Node;
use PhpParser\NodeTraverser;

class FindInvalidRouteDefinitions extends FindInvalidRouteCalls
{
    public function findRouteFunctionCalls($file)
    {
        dump($this->parser->parse(file_get_contents($file)));

        $traverser = new NodeTraverser;
        $visitor = new FindingVisitor(function (Node $node) use ($file) {
            if ($node instanceof Node\Expr\StaticCall) {
                if ($node->name instanceof Node\Name && $node->name->toString() === 'route') {
                    $firstArgument = $node->args[0];

                    if ($firstArgument->value instanceof Node\Scalar\String_) {
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
        $traverser->traverse($this->parser->parse(file_get_contents($file)));

        return $visitor->getFoundNodes();
    }
}
