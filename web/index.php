<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$dataFile = __DIR__.'/data.json';

$app          = new Silex\Application();
$app['debug'] = true;
$app['title'] = 'RaspSwitcher v0.8';

// load Data from file
$app['data']  = fetchData($dataFile);

// load i18n
$app['i18n']  = json_decode(file_get_contents(__DIR__.'/i18n/'.$app['data']['locale'].'.json'), true);

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
	return $app['twig']->render('about.twig');
})->bind('about');

$app->get('/settings', function() use ($app) {

	$dir = __DIR__.'/i18n';
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh)))
		$files[] = $filename;

	sort($files);
	
	$aLangFiles = array();
	
	foreach($files AS $key => $file)
	{
		if (strpos($file, '.json') !== false)
		{
			$fContent = json_decode(file_get_contents(__DIR__.'/i18n/'.$file), true);

			if (isset($fContent['locale']))
				$aLangFiles[substr($file, 0, -5)] = $fContent['locale'];
		}
	}

	return $app['twig']->render('settings.twig', array('aLangFiles' => $aLangFiles));
})->bind('settings');

// save new/changed switch
$app->post('/settings/save', function (Request $request) use ($app, $dataFile) {

	$language = $request->get('locale');

	
	// save settings
	$aData = $app['data'];
	
	$aData['locale'] = $language;

	saveData($aData, $dataFile);

	$app['data'] = fetchData($dataFile);

	return $app->redirect($app['url_generator']->generate('settings'));

})->bind('settings-save');

$app->get('/switches/edit', function() use ($app) {
	return $app['twig']->render('switches.twig');
})->bind('switches');

$app->get('/switch/edit/{id}', function($id) use ($app) {
	return $app['twig']->render('switch_edit.twig', array('id' => $id));
})->bind('switch-edit');

$app->get('/switch/new', function() use ($app) {
	if (sizeof($app['data']['groups']))
		return $app['twig']->render('switch_edit.twig', array('id' => 0));
	else
	{
		$app['session']->set('flash', array(
			'type'  => 'danger',
			'short' => $app['i18n']['text']['error'],
			'ext'   => $app['i18n']['errors']['no_groups'],
		));
	
		return $app->redirect($app['url_generator']->generate('groups'));
	}
})->bind('switch-new');

$app->get('/switch/delete/{id}', function($id) use ($app, $dataFile) {

	// delete switch
	$aData = $app['data'];
	
	unset($aData['switches'][$id]);

	saveData($aData, $dataFile);

	$app['data'] = fetchData($dataFile);
	
	return $app->redirect($app['url_generator']->generate('switches'));
})->bind('switch-delete');

// save new/changed switch
$app->post('/switch/save', function (Request $request) use ($app, $dataFile) {

	$switchId = $request->get('id');

	$switch           = array();
	$switch['name']   = $request->get('name');
	$switch['group']  = $request->get('group');
	$switch['config'] = '';

	if (strlen($switch['name']) < 3)
	{
		$app['session']->set('flash', array(
			'type'  => 'danger',
			'short' => $app['i18n']['text']['error'],
			'ext'   => $app['i18n']['errors']['no_name_given'],
		));
	
		if ($switchId < 1)
			return $app->redirect($app['url_generator']->generate('switch-new'));
		else
			return $app->redirect($app['url_generator']->generate('switch-edit', array('id' => $id)));
	}
	
	for ($i = 1; $i <= 5; $i++)
		$switch['config'] .= (is_null($request->get('check_'.$i)))? '0':'1';
	
	for ($i = 'A'; $i <= 'E'; $i++)
		$switch['config'] .= (is_null($request->get('check_'.$i)))? '0':'1';

	
	// save switch
	$aData = $app['data'];
	
	// new group or just an edit?
	if ($switchId >= 1)
		$aData['switches'][$switchId] = $switch;
	else
	{
		if (!sizeof($aData['switches']))
			$aData['switches'][1] = $switch;
		else
			$aData['switches'][] = $switch;
	}

	saveData($aData, $dataFile);

	$app['data'] = fetchData($dataFile);

	return $app->redirect($app['url_generator']->generate('switches'));

})->bind('switch-save');


$app->get('/groups/edit', function() use ($app) {
	return $app['twig']->render('groups.twig');
})->bind('groups');

$app->get('/group/edit/{id}', function($id) use ($app) {
	return $app['twig']->render('group_edit.twig', array('id' => $id));
})->bind('group-edit');

$app->get('/group/new', function() use ($app) {
	return $app['twig']->render('group_edit.twig', array('id' => 0));
})->bind('group-new');

$app->get('/group/delete/{id}', function($id) use ($app, $dataFile) {

	if (isset($app['data']['switches']))
	{
		foreach($app['data']['switches'] AS $switch)
		{
			if ($switch['group'] == $id)
			{
				$app['session']->set('flash', array(
					'type'  => 'danger',
					'short' => $app['i18n']['text']['error'],
					'ext'   => $app['i18n']['errors']['group_not_empty'],
				));

				return $app->redirect($app['url_generator']->generate('groups'));
			}
		}
	}
	
	// delete group
	$aData = $app['data'];
	
	unset($aData['groups'][$id]);

	saveData($aData, $dataFile);

	$app['data'] = fetchData($dataFile);

	return $app->redirect($app['url_generator']->generate('groups'));
})->bind('group-delete');

// save new/changed group
$app->post('/group/save', function (Request $request) use ($app, $dataFile) {

	$groupId   = $request->get('id');
	$groupName = $request->get('name');

	if (strlen($groupName) < 3)
	{
		$app['session']->set('flash', array(
			'type'  => 'danger',
			'short' => $app['i18n']['text']['error'],
			'ext'   => $app['i18n']['errors']['no_name_given'],
		));
	
		if ($groupId < 1)
			return $app->redirect($app['url_generator']->generate('group-new'));
		else
			return $app->redirect($app['url_generator']->generate('group-edit', array('id' => $id)));
	}
	
	foreach($app['data']['groups'] AS $key => $group)
	{
		if ($group == $groupName && $key <> $groupId)
		{
			$app['session']->set('flash', array(
			'type'  => 'danger',
			'short' => $app['i18n']['text']['error'],
			'ext'   => $app['i18n']['errors']['groupname_occupied'],
		));
	
		if ($groupId < 1)
			return $app->redirect($app['url_generator']->generate('group-new'));
		else
			return $app->redirect($app['url_generator']->generate('group-edit', array('id' => $id)));
		}
	}
	
	// save group
	$aData = $app['data'];
	
	// new group or just an edit?
	if ($groupId >= 1)
		$aData['groups'][$groupId] = $groupName;
	else
	{
		if (!sizeof($aData['groups']))
			$aData['groups'][1] = $groupName;
		else
			$aData['groups'][] = $groupName;
	}

	saveData($aData, $dataFile);

	$app['data'] = fetchData($dataFile);

	return $app->redirect($app['url_generator']->generate('groups'));

})->bind('group-save');

function fetchData($file)
{
	if (!file_exists($file))
		file_put_contents($file, json_encode(array()));

	$aData = json_decode(file_get_contents($file), true);

	if (isset($aData['groups']))
		asort($aData['groups']);
	else
		$aData['groups'] = array();

	$aData['aFilledGroups'] = getFilledGroups($aData);
	
	if (!isset($aData['locale']))
		$aData['locale'] = 'en';

	return $aData;
}

function saveData(array $aData, $file)
{
	if (isset($aData['aFilledGroups']))
		unset($aData['aFilledGroups']);

	file_put_contents($file, utf8_encode(json_encode($aData)));

	return true;
}

function getFilledGroups($aData)
{
	$aFilledGroups = array();
	
	if (isset($aData['groups']) && isset($aData['switches']))
	{
		foreach($aData['groups'] AS $kG => $group)
		{
			foreach($aData['switches'] AS $switch)
				if ($switch['group'] == $kG)
					$aFilledGroups[$kG] = $group;
		}
	}
	
	asort($aFilledGroups);

	return $aFilledGroups;
}


// run
$app->run();
