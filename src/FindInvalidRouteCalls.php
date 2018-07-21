<?php

namespace Juddling\RouteChecker;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;

class FindInvalidRouteCalls extends FindInvalid
{
	/** @var Collection */
	protected $routeNames;
	protected $nameOfArgument = 'Route Names';

	public function __construct()
	{
		$this->routeNames = collect(\Route::getRoutes())
			->map(function (Route $route) {
				return $route->getName();
			})->filter();

		parent::__construct();
	}

	protected function getFunctionName()
	{
		return 'route';
	}

	protected function check(string $routeName): bool
	{
		return $this->routeNames->contains($routeName);
	}
}
