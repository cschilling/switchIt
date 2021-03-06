<?php
namespace SwitchIt\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\ControllerProviderInterface;
use SwitchIt\Data;


class CronjobControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controllers = $app['controllers_factory'];


		/**
		 * show cronjobs
		 *
		 */
		$controllers->get('/', function(Application $app) {
			$time = date("Y, n, j, G, i, s");
			return $app['twig']->render('cronjobs/cronjobs.html', array('time' => $time));
		})->bind('cron');


		/**
		 * add cronjob
		 *
		 */
		$controllers->get('/new', function(Application $app) {

			if (!sizeof($app['data']['switches']))
			{
				$app['session']->set('flash', array(
					'type'  => 'danger',
					'short' => $app['i18n']['text']['error'],
					'ext'   => $app['i18n']['errors']['no_switch'],
				));

				return $app->redirect($app['url_generator']->generate('switches'));
			}

			if (isset($app['data']['options']['location']['lat']))
				$locationIsSet = true;
			else
				$locationIsSet = false;

			if ($locationIsSet)
			{
				$location = $app['data']['options']['location'];

				$sunrise = date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP , $location['lat'], $location['lng'], 96);
				$sunset  = date_sunset (time(), SUNFUNCS_RET_TIMESTAMP , $location['lat'], $location['lng'], 96);
			}
			else
			{
				$sunrise = date('U', mktime (0, 0, 0));
				$sunset  = date('U', mktime (0, 0, 0));
			}

			return $app['twig']->render('cronjobs/cronjob_edit.html', array('id' => 0, 'sunrise' => $sunrise, 'sunset' => $sunset, 'locationIsSet' => $locationIsSet));
		})->bind('cron-new');


		/**
		 * edit cronjob
		 *
		 */
		$controllers->get('/edit/{id}', function(Application $app, $id) {

			if (!sizeof($app['data']['switches']))
			{
				$app['session']->set('flash', array(
					'type'  => 'danger',
					'short' => $app['i18n']['text']['error'],
					'ext'   => $app['i18n']['errors']['no_switch'],
				));

				return $app->redirect($app['url_generator']->generate('switches'));
			}

			if (isset($app['data']['options']['location']['lat']))
				$locationIsSet = true;
			else
				$locationIsSet = false;

			if ($locationIsSet)
			{
				$location = $app['data']['options']['location'];

				$sunrise = date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP , $location['lat'], $location['lng'], 96);
				$sunset  = date_sunset (time(), SUNFUNCS_RET_TIMESTAMP , $location['lat'], $location['lng'], 96);
			}
			else
			{
				$sunrise = date('U', mktime (0, 0, 0));
				$sunset  = date('U', mktime (0, 0, 0));
			}

			return $app['twig']->render('cronjobs/cronjob_edit.html', array('id' => $id, 'sunrise' => $sunrise, 'sunset' => $sunset, 'locationIsSet' => $locationIsSet));
		})->bind('cron-edit');


		/**
		 * delete cronjob
		 *
		 */
		$controllers->get('/cronjob/delete/{id}', function(Application $app, $id) {

			// delete cronjob
			$aData = $app['data'];

			unset($aData['cronjobs'][$id]);

			$data = new Data($app['dataFile']);
			$data->saveData($aData);

			$app['data'] = $data->fetchData();

			return $app->redirect($app['url_generator']->generate('cron'));
		})->bind('cron-delete');


		/**
		 * save changes
		 *
		 */
		$controllers->post('/cronjob/save', function (Application $app, Request $request) {

			$cronjobId = $request->get('id');
			$aSwitches = $app['data']['switches'];
			$aToSwitch = array();

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
			$aCronjob['type']     = (int)$request->get('type');
			$aCronjob['offset']   = (int)$request->get('offset');
			$aCronjob['switches'] = $aToSwitch;

			// check for errors
			if (!isset($app['data']['options']['location']['lat']) && $aCronjob['type'] > 0)
			{
				$error = true;

				$app['session']->set('flash', array(
					'type'  => 'danger',
					'short' => $app['i18n']['text']['error'],
					'ext'   => $app['i18n']['text']['location_not_set'],
				));
			}
			elseif (strlen($aCronjob['name']) < 3)
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
					return $app->redirect($app['url_generator']->generate('cron-edit', array('id' => $cronjobId)));
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

			$data = new Data($app['dataFile']);
			$data->saveData($aData);

			$app['data'] = $data->fetchData();

			return $app->redirect($app['url_generator']->generate('cron'));
		})->bind('cron-save');


		return $controllers;
	}
}
