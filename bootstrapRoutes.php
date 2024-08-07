<?php
declare(strict_types=1);

//use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Slim\{App, Interfaces\RouteCollectorProxyInterface as Group};


return function (&$c, $debugMode = null) {
	$app = $c->get(App::class);
	$psr4PathHelper = $c->get('psr4PathHelper');
	$debugMode ?? (require_once $psr4PathHelper->findFile('hSlim/base/AutoController.php', true))($c);
	return [$app, $psr4PathHelper];
};
