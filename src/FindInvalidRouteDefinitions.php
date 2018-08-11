<?php

namespace Juddling\RouteChecker;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;

class FindInvalidRouteDefinitions extends FindInvalid
{
    protected $nameOfArgument = 'Route Controller Action';

    public function findFunctionCalls($file)
    {
        $traverser = new NodeTraverser;
        $visitor = new FindingVisitor($this->nodeVisitor($file));
        $traverser->addVisitor($visitor);
        $traverser->traverse($this->parser->parse(file_get_contents($file)));
        return $visitor->getFoundNodes();
    }

    protected function nodeVisitor($file): callable
    {
        return function (Node $node) use ($file) {
            if ($node instanceof Node\Expr\StaticCall) {
                foreach ($node->args as $argument) {
                    $routeConfiguration = $argument->value;
                    if ($routeConfiguration instanceof Node\Expr\Array_) {
                        foreach ($routeConfiguration->items as $routeConfigurationItem) {
                            if ($routeConfigurationItem->key instanceof Node\Scalar\String_ && $routeConfigurationItem->key->value === 'uses') {
                                $action = $routeConfigurationItem->value->value;

                                $this->results->push([
                                    'name' => $action,
                                    'valid' => $this->check($action),
                                    'file' => $file,
                                ]);

                                return true;
                            }
                        }
                    }
                }
            }

            return false;
        };
    }

    protected function check(string $argument)
    {
        [$className, $methodName] = explode('@', $argument);
        $fullyQualifiedName = "App\Http\Controllers\\$className";
        return in_array($methodName, get_class_methods($fullyQualifiedName));
    }

    protected function getFunctionName()
    {
        throw new \RuntimeException;
    }
}
