<?php
require_once __DIR__.'/../vendor/autoload.php';

include("switch.php");

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$dataFile = __DIR__.'/data.json';

$app            = new Silex\Application();
$app['debug']   = true;
$app['title']   = 'SwitchIt';
$app['version'] = '0.9';

// load Data from file
$app['data']  = fetchData($dataFile);

// load i18n
$app['i18n']  = json_decode(file_get_contents(__DIR__.'/i18n/'.$app['data']['options']['locale'].'.json'), true);

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
	return $app['twig']->render('control/control.twig');
})->bind('home');

$app->get('/about', function() use ($app) {
	return $app['twig']->render('about.twig');
})->bind('about');

$app->post('/switch', function (Request $request) use ($app, $dataFile) {

	$aToSwitch = array();
	
	if (!is_null($request->get('switchId')) && $request->get('switchId') > 0)
	{
		$aToSwitch = array(0 => $request->get('switchId'));
	}
	elseif (!is_null($request->get('groupId')) && $request->get('groupId') > 0)
	{
		foreach($app['data']['switches'] AS $switchKey => $switch)
		{
			if ($switch['group'] == $request->get('groupId'))
				$aToSwitch[] = $switchKey;
		}
	}
	elseif (!is_null($request->get('all')) && $request->get('all') > 0)
	{
		foreach($app['data']['switches'] AS $switchKey => $switch)
			$aToSwitch[] = $switchKey;
	}

	$switchIt = new switchIt();

	foreach($aToSwitch AS $switchKey)
	{
		$switch = $app['data']['switches'][$switchKey];
		
		// switch it!
		$switchIt->doSwitch($switch['config'], $switch['number'], $request->get('switchOn'), $app['data']['options']['delay'], $app['data']['options']['server'], $app['data']['options']['port']);
	}

	return true;

})->bind('switch-it');


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

	return $app['twig']->render('settings/settings.twig', array('aLangFiles' => $aLangFiles));
})->bind('settings');

// save new/changed switch
$app->post('/settings/save', function (Request $request) use ($app, $dataFile) {

	// save settings
	$aData = $app['data'];
	
	if (!is_null($request->files->get('file')))
	{
		$fContent = json_decode(file_get_contents($request->files->get('file')), true);

		if (isset($fContent['options']['locale']))
		{
			file_put_contents($dataFile, file_get_contents($request->files->get('file')));
			
			$app['data'] = fetchData($dataFile);
			
			$app['session']->set('flash', array(
				'type'  => 'success',
				'short' => $app['i18n']['text']['info'],
				'ext'   => $app['i18n']['text']['data_restored'],
			));

			return $app->redirect($app['url_generator']->generate('settings'));
		}
		else
		{
			$app['session']->set('flash', array(
				'type'  => 'danger',
				'short' => $app['i18n']['text']['error'],
				'ext'   => $app['i18n']['errors']['datafile_invalid'],
			));
			
			return $app->redirect($app['url_generator']->generate('settings'));
		}
	}

	$aData['options']['locale'] = $request->get('locale');
	$aData['options']['server'] = $request->get('server');
	$aData['options']['port']   = $request->get('port');
	$aData['options']['delay']  = $request->get('delay');

	saveData($aData, $dataFile);

	$app['data'] = fetchData($dataFile);

	return $app->redirect($app['url_generator']->generate('settings'));

})->bind('settings-save');

$app->match('settings/export', function() use($app, $dataFile) {

	$stream = function () use ($dataFile) {
        readfile($dataFile);
    };

	return $app->stream($stream, 200, array(
		'Content-Type'        => 'application/json',
		'Content-length'      => filesize($dataFile),
		'Content-Disposition' => 'attachment; filename="data.json"'	
	));
})->bind('settings-export');

$app->get('/switches/edit', function() use ($app) {
	return $app['twig']->render('switches/switches.twig');
})->bind('switches');

$app->get('/switch/edit/{id}', function($id) use ($app) {
	return $app['twig']->render('switches/switch_edit.twig', array('id' => $id));
})->bind('switch-edit');

$app->get('/switch/new', function() use ($app) {
	if (sizeof($app['data']['groups']))
		return $app['twig']->render('switches/switch_edit.twig', array('id' => 0));
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
	$switch['number'] = str_pad($request->get('number'), 2, '0', STR_PAD_LEFT);
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
	return $app['twig']->render('groups/groups.twig');
})->bind('groups');

$app->get('/group/edit/{id}', function($id) use ($app) {
	return $app['twig']->render('groups/group_edit.twig', array('id' => $id));
})->bind('group-edit');

$app->get('/group/new', function() use ($app) {
	return $app['twig']->render('groups/group_edit.twig', array('id' => 0));
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

$app->get('/cron', function() use ($app) {
	$time = date("Y, n, j, G, i, s");
	return $app['twig']->render('cronjobs/cronjobs.twig', array('time' => $time));
})->bind('cron');

$app->get('/cron/new', function() use ($app) {
	return $app['twig']->render('cronjobs/cronjob_edit.twig', array('id' => 0));
})->bind('cron-new');

$app->get('/cronjob/edit/{id}', function($id) use ($app) {
	return $app['twig']->render('cronjobs/cronjob_edit.twig', array('id' => $id));
})->bind('cron-edit');

$app->get('/cronjob/delete/{id}', function($id) use ($app, $dataFile) {

	// delete cronjob
	$aData = $app['data'];
	
	unset($aData['cronjobs'][$id]);

	saveData($aData, $dataFile);

	$app['data'] = fetchData($dataFile);
	
	return $app->redirect($app['url_generator']->generate('cron'));
})->bind('cron-delete');

$app->post('/cronjob/save', function (Request $request) use ($app, $dataFile) {

	$cronjobId = $request->get('id');
	$aSwitches = $app['data']['switches'];

	// get switches that should be set
	foreach($aSwitches AS $key => $switch)
	{
		if (!is_null($request->get('switch_'.$key)))
			$aToSwitch[$key] = (is_null($request->get('set_switch_'.$key)))? '0':'1';
	}

	// get days on which the cronjob should fire
	$days = '';
	for($i = 1; $i <= 7; $i++)
	{
		if (!is_null($request->get('day_'.$i)))
			$days .= '1';
		else
			$days .= '0';
	}

	$aCronjob             = array();
	$aCronjob['name']     = $request->get('name');
	$aCronjob['time']     = (((int)$request->get('time_hour') * 60) + (int)$request->get('time_minute'));
	$aCronjob['days']     = $days;
	$aCronjob['switches'] = $aToSwitch;


	if (strlen($aCronjob['name']) < 3)
	{
		$error = true;

		$app['session']->set('flash', array(
			'type'  => 'danger',
			'short' => $app['i18n']['text']['error'],
			'ext'   => $app['i18n']['errors']['no_name_given'],
		));
	}
	elseif($aCronjob['days'] === '0000000')
	{
		$error = true;

		$app['session']->set('flash', array(
			'type'  => 'danger',
			'short' => $app['i18n']['text']['error'],
			'ext'   => $app['i18n']['errors']['no_days_selected'],
		));
	}
	elseif(!sizeof($aCronjob['switches']))
	{
		$error = true;

		$app['session']->set('flash', array(
			'type'  => 'danger',
			'short' => $app['i18n']['text']['error'],
			'ext'   => $app['i18n']['errors']['no_switches_selected'],
		));
	}
	else
		$error = false;

	// on error, redirect
	if ($error)
	{
		if ($cronjobId < 1)
			return $app->redirect($app['url_generator']->generate('cron-new'));
		else
			return $app->redirect($app['url_generator']->generate('cron-edit', array('id' => $id)));
	}

	
	// save cronjob
	$aData = $app['data'];
	
	// new cronjob or just an edit?
	if ($cronjobId >= 1)
		$aData['cronjobs'][$cronjobId] = $aCronjob;
	else
	{
		if (!sizeof($aData['cronjobs']))
			$aData['cronjobs'][1] = $aCronjob;
		else
			$aData['cronjobs'][] = $aCronjob;
	}

	saveData($aData, $dataFile);

	$app['data'] = fetchData($dataFile);

	return $app->redirect($app['url_generator']->generate('cron'));
})->bind('cron-save');


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
	
	if (!isset($aData['options']['locale']))
		$aData['options']['locale'] = 'en';
	
	if (!isset($aData['options']['server']))
		$aData['options']['server'] = '127.0.0.1';
	
	if (!isset($aData['options']['port']))
		$aData['options']['port'] = '11337';
		
	if (!isset($aData['options']['delay']))
		$aData['options']['delay'] = '0';


	if (!isset($aData['cronjobs']))
		$aData['cronjobs'] = array();
		
	asort($aData['cronjobs']);

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
