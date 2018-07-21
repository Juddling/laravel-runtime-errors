<?php

namespace Juddling\RouteChecker;

use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\OutputInterface;

class FindInvalidViewCalls extends FindInvalid
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
		/** @var FileViewFinder $views */
		$views = app('view.finder');

		try {
			$views->find($viewPath);
		} catch (InvalidArgumentException $e) {
			return false;
		}

		return true;
	}
}
