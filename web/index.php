<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;
$app['title'] = 'RaspSwitcher v0.2';

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app['i18n'] = json_decode(file_get_contents(__DIR__.'/i18n/de.json'), true);

// routing
$app->get('/', function() use ($app) {
	return $app['twig']->render('show.twig');
})->bind('home');

$app->get('/about', function() use ($app) {
	return $app['twig']->render('info.twig');
})->bind('about');

$app->get('/edit-switches', function() use ($app) {
	return $app['twig']->render('switches.twig');
})->bind('switches');

$app->get('/edit-switch/{id}', function($id) use ($app) {
	return $app['twig']->render('edit_switch.twig', array('id' => $id));
})->bind('edit-switch');



$app->get('/edit-groups', function() use ($app) {
	return $app['twig']->render('groups.twig');
})->bind('groups');

// run
$app->run();
