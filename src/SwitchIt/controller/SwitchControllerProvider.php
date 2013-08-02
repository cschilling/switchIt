<?php
namespace SwitchIt\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\ControllerProviderInterface;
use SwitchIt\Data;


class SwitchControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
    {
		$controllers = $app['controllers_factory'];


	    /**
	     * Control switches (set on/off)
	     *
	     */
	    $controllers->get('/', function(Application $app) {
			return $app['twig']->render('switches/control.html');
		})->bind('control');


	    /**
	     * edit/add switches
	     *
	     */
	    $controllers->get('/show', function(Application $app) {
			return $app['twig']->render('switches/switches.html');
		})->bind('switches');


	    /**
	     * edit a switch
	     *
	     */
	    $controllers->get('/edit/{id}', function(Application $app, $id) {
			return $app['twig']->render('switches/switch_edit.html', array('id' => $id));
		})->bind('switch-edit');


	    /**
	     * add a switch
	     *
	     */
	    $controllers->get('/new', function(Application $app) {
			if (sizeof($app['data']['groups']))
				return $app['twig']->render('switches/switch_edit.html', array('id' => 0));
			else
			{
				$app['session']->set('flash', array(
					'type'  => 'danger',
					'short' => $app['i18n']['text']['error'],
					'ext'   => $app['i18n']['errors']['no_group'],
				));

				return $app->redirect($app['url_generator']->generate('groups'));
			}
		})->bind('switch-new');


	    /**
	     * delete a switch
	     *
	     */
	    $controllers->get('/delete/{id}', function(Application $app, $id) {

			$aData = $app['data'];

		    // delete the switch from cronjobs
		    foreach($aData['cronjobs'] AS $key => $cron)
		    {
			    if (isset($cron['switches'][$id]))
				    unset($aData['cronjobs'][$key]['switches'][$id]);
		    }

		    // delete switch
			unset($aData['switches'][$id]);

			$data = new Data($app['dataFile']);
			$data->saveData($aData);

			$app['data'] = $data->fetchData();

			return $app->redirect($app['url_generator']->generate('switches'));
		})->bind('switch-delete');


	    /**
	     * save changes
	     *
	     */
	    $controllers->post('/save', function (Application $app, Request $request) {

			$switchId = $request->get('id');

			$switch           = array();
			$switch['name']   = $request->get('name');
			$switch['group']  = $request->get('group');
			$switch['number'] = $request->get('number');
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
					return $app->redirect($app['url_generator']->generate('switch-edit', array('id' => $switchId)));
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

			$data = new Data($app['dataFile']);
			$data->saveData($aData);

			$app['data'] = $data->fetchData();

			return $app->redirect($app['url_generator']->generate('switches'));

		})->bind('switch-save');


	    /**
	     * flip one or more switches
	     *
	     */
	    $controllers->post('/switch', function (Application $app, Request $request) {

			$aSwitchTmp = array();

			if (!is_null($request->get('switchId')) && $request->get('switchId') > 0)
			{
				$aSwitchTmp = array(0 => $request->get('switchId'));
			}
			elseif (!is_null($request->get('groupId')) && $request->get('groupId') > 0)
			{
				foreach($app['data']['switches'] AS $switchKey => $switch)
				{
					if ($switch['group'] == $request->get('groupId'))
						$aSwitchTmp[] = $switchKey;
				}
			}
			elseif (!is_null($request->get('all')) && $request->get('all') > 0)
			{
				foreach($app['data']['switches'] AS $switchKey => $switch)
					$aSwitchTmp[] = $switchKey;
			}

			$aToSwitch = array();
			foreach($aSwitchTmp AS $switchKey)
			{
				$switch = $app['data']['switches'][$switchKey];

				$aToSwitch[] = $switch['config'].' '.(int)$switch['number'].' '.(int)$request->get('switchOn');
			}

			if (file_put_contents($app['switchFile'], json_encode($aToSwitch)))
				return true;
			else
				return $app['i18n']['errors']['switches_not_set'];

		})->bind('switch-it');


		return $controllers;
    }
}
