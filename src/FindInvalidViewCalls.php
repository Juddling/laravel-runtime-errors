<?php

namespace Juddling\RouteChecker;

use Illuminate\Support\Collection;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;

class FindInvalidViewCalls extends FindInvalid
{
	/** @var Collection */
	protected $viewPaths;
	/** @var string $nameOfArgument */
	protected $nameOfArgument = 'View Path';
	/** @var FileViewFinder $views */
	protected $viewFinder;

	public function __construct()
	{
		$this->viewFinder = app('view.finder');
		parent::__construct();
	}

	protected function getFunctionName()
	{
		return 'view';
	}

	protected function check(string $viewPath): bool
	{
		try {
			$this->viewFinder->find($viewPath);
		} catch (InvalidArgumentException $e) {
			return false;
		}

		return true;
	}
}
