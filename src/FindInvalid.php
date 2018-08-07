<?php

namespace Juddling\RouteChecker;

use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\OutputInterface;

abstract class FindInvalid
{
    protected $parser;
    /** @var Collection */
    protected $routeCalls;

    protected $nameOfArgument = 'Parameter';

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->results = new Collection;
    }

    abstract protected function check(string $argument);

    abstract protected function getFunctionName();

    public function findFunctionCalls($file)
    {
        $code = file_get_contents($file);

        $traverser = new NodeTraverser;
        $visitor = new FindingVisitor(function (Node $node) use ($file) {
            if ($node instanceof Node\Expr\FuncCall) {
                if ($node->name instanceof Node\Name && $node->name->toString() === $this->getFunctionName()) {
                    $firstArgument = $node->args[0];

                    if ($firstArgument->value instanceof \PhpParser\Node\Scalar\String_) {
                        // value of first argument is route name
                        $stringScalar = $firstArgument->value;
                        $firstArgument = $stringScalar->value;

                        $this->results->push([
                            'name' => $firstArgument,
                            'valid' => $this->check($firstArgument),
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
        $table->setHeaders([$this->nameOfArgument, 'Valid', 'File']);

        $table->addRows($this->results->sortBy('valid')->map(function ($call) {
            $call['valid'] = $call['valid'] ? '✅' : '❌';
            return $call;
        })->toArray());

        $table->setStyle('borderless');
        $table->render();
    }
}
