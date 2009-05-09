Routing Debugger for Nette Framework
------------------------------------
Copyright (c) 2009 David Grudl


Usage:

1) Setup your routes in bootstrap.php


	$router = $application->getRouter();

	$router[] = new Route('index.php', array(
		'presenter' => 'Dashboard',
		'action' => 'default',
	), Route::ONE_WAY);

	$router[] = new Route('<presenter>/<action>/<id>', array(
		'presenter' => 'Dashboard',
		'action' => 'default',
		'id' => NULL,
	));



2) Run debugger. Add this lines above command $application->run()

	require 'RoutingDebugger.php';
	RoutingDebugger::run();
