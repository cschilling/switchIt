<?php
namespace SwitchIt\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\ControllerProviderInterface;


class ApiControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controllers = $app['controllers_factory'];


		/**
		 * switch via api
		 *
		 * // example
		 * http://192.168.1.205/api/switch?aSwitch[]=00011_3_1

		 * where:
		 *   00011 => switch-config
		 *       3 => switch-number
		 *     1/0/2 => on/off/flip
		 *
		 *
		 */
		$controllers->get('/switch', function (Application $app, Request $request)
		{
			$aSwitches = $request->get('aSwitch');

			$aToSwitch = array();
			foreach($aSwitches AS $key => $switchConfig)
			{
				$aSwitchConfig = explode('_', $switchConfig);

				// check for right format
				if (!isset($aSwitchConfig[2]) || ((int)$aSwitchConfig[2] <> 0 && (int)$aSwitchConfig[2] <> 1))
					return new Response('Bad Request: Key #'.$key.' ("'.$switchConfig.'") malformated', 300);

				$aToSwitch[] = $aSwitchConfig[0].' '.(int)$aSwitchConfig[1].' '.(int)$aSwitchConfig[2];
			}

			if (file_put_contents($app['switchFile'], json_encode($aToSwitch)))
				return new Response('ok', 200);
			else
				return new Response('Could not open "'.$app['switchFile'].'" for writing', 500);

		})->bind('api-switch');

        return $controllers;
	}
}
