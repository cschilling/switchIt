<?php
namespace SwitchIt\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\ControllerProviderInterface;


class ContentControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controllers = $app['controllers_factory'];


		/**
		 * homepage
		 * (redirect to switch-control)
		 *
		 */
		$controllers->get('/', function (Application $app)
		{
			return $app->redirect($app['url_generator']->generate('control'));
		})->bind('homepage');


		/**
		 * about-page
		 *
		 */
		$controllers->get('/about', function (Application $app)
		{
			return $app['twig']->render('static/about.html', array());
		})->bind('about');

		return $controllers;
	}
}
