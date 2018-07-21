<?php

namespace Juddling\RouteChecker;

use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\OutputInterface;

class FindInvalidRouteCalls extends FindInvalid
{
	/** @var Collection */
	protected $viewPaths;
	protected $nameOfArgument = 'View Path';


	protected function getFunctionName()
	{
		return 'view';
	}

	protected function check(string $viewPath): bool
	{
		if ($this->viewPaths) {
			return $this->viewPaths->contains($viewPath);
		}

		// Check if the view is present

		// now try this function again
		return $this->check($viewPath);
	}
}
