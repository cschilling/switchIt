<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app          = new Silex\Application();
$app['debug'] = true;
$app['title'] = 'RaspSwitcher v0.3';
$app['i18n']  = json_decode(file_get_contents(__DIR__.'/i18n/de.json'), true);

// set data
$data  = json_decode(file_get_contents(__DIR__.'/data.json'),    true);

asort($data['groups']);

$app['data'] = $data;

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->before(function() use ($app) {
    $flash = $app['session']->get('flash');
    $app['session']->set('flash', null);

    if (!empty( $flash ))
		$app['twig']->addGlobal('flash', $flash);
});

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

$app->get('/new-switch', function() use ($app) {
	return $app['twig']->render('edit_switch.twig', array('id' => 0));
})->bind('new-switch');

// save new/changed switch
$app->post('/save-switch', function (Request $request) use ($app) {

	$switchId = $request->get('id');

	$switch           = array();
	$switch['name']   = $request->get('name');
	$switch['group']  = $request->get('group');
	$switch['config'] = '';
	
	if (strlen($switch['name']) < 3)
	{
		$app['session']->set('flash', array(
			'type'  => 'danger', //other possible values include 'warning', 'info', 'success' - it's part of Twitter Bootstrap
			'short' => $app['i18n']['text']['error'],
			'ext'   => $app['i18n']['errors']['no_name_given'],
		));
	
		if ($switchId < 1)
			return $app->redirect($app['url_generator']->generate('new-switch'));
		else
			return $app->redirect($app['url_generator']->generate('edit-switch', array('id' => $id)));
	}
	
	for ($i = 1; $i <= 5; $i++)
		$switch['config'] .= (is_null($request->get('check_'.$i)))? '0':'1';
	
	for ($i = 'A'; $i <= 'E'; $i++)
		$switch['config'] .= (is_null($request->get('check_'.$i)))? '0':'1';

	echo '<pre>';
	print_r($switch);
	die;

	return $app->redirect('/edit-switch/'.$id);
})->bind('save-switch');



$app->get('/edit-groups', function() use ($app) {
	return $app['twig']->render('groups.twig');
})->bind('groups');

// run
$app->run();
