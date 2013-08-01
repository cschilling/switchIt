<?php
namespace SwitchIt\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\ControllerProviderInterface;
use SwitchIt\Data;


class SettingsControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controllers = $app['controllers_factory'];


		/**
		 * show settings
		 *
		 */
		$controllers->get('/', function(Application $app) {

			$dir = __DIR__.'/../../../i18n';
			$dh  = opendir($dir);
			while (false !== ($filename = readdir($dh)))
				$files[] = $filename;

			sort($files);

			$aLangFiles = array();

			foreach($files AS $key => $file)
			{
				if (strpos($file, '.json') !== false)
				{
					$fContent = json_decode(file_get_contents($dir.'/'.$file), true);

					if (isset($fContent['locale']))
						$aLangFiles[substr($file, 0, -5)] = $fContent['locale'];
				}
			}

			return $app['twig']->render('settings/settings.html', array('aLangFiles' => $aLangFiles));
		})->bind('settings');


		/**
		 * save settings
		 *
		 */
		$controllers->post('/save', function (Application $app, Request $request) {

			// save settings
			$aData = $app['data'];

			if (!is_null($request->files->get('file')))
			{
				$fContent = json_decode(file_get_contents($request->files->get('file')), true);

				if (isset($fContent['options']['locale']))
				{
					file_put_contents($app['dataFile'], file_get_contents($request->files->get('file')));

					$data = new Data($app['dataFile']);

					$app['data'] = $data->fetchData();

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

			$data = new Data($app['dataFile']);
			$data->saveData($aData);

			$app['data'] = $data->fetchData();

			return $app->redirect($app['url_generator']->generate('settings'));

		})->bind('settings-save');


		/**
		 * export settings
		 *
		 */
		$controllers->match('/export', function(Application $app) {

			$file = $app['dataFile'];

			$stream = function () use ($file) {
		        readfile($file);
		    };

			return $app->stream($stream, 200, array(
				'Content-Type'        => 'application/json',
				'Content-length'      => filesize($file),
				'Content-Disposition' => 'attachment; filename="data.json"'
			));
		})->bind('settings-export');


		return $controllers;
	}
}
