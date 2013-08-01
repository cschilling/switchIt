<?php
namespace SwitchIt\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\ControllerProviderInterface;
use SwitchIt\Data;


class GroupControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
    {
		$controllers = $app['controllers_factory'];


	    /**
	     * show/edit/add groups
	     *
	     */
	    $controllers->get('/', function(Application $app) {
			return $app['twig']->render('groups/groups.html');
		})->bind('groups');


	    /**
	     * edit a group
	     *
	     */
	    $controllers->get('/edit/{id}', function(Application $app, $id) {
			return $app['twig']->render('groups/group_edit.html', array('id' => $id));
		})->bind('group-edit');


	    /**
	     * add a group
	     *
	     */
	    $controllers->get('/new', function(Application $app) {
			return $app['twig']->render('groups/group_edit.html', array('id' => 0));
		})->bind('group-new');


	    /**
	     * delete a group
	     *
	     */
	    $controllers->get('/delete/{id}', function(Application $app, $id) {
		
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
		
			$data = new Data($app['dataFile']);
			$data->saveData($aData);

			$app['data'] = $data->fetchData();
		
			return $app->redirect($app['url_generator']->generate('groups'));
		})->bind('group-delete');


	    /**
	     * save changes
	     *
 	     */
	    $controllers->post('/save', function (Application $app, Request $request) {
		
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
					return $app->redirect($app['url_generator']->generate('group-edit', array('id' => $groupId)));
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
						return $app->redirect($app['url_generator']->generate('group-edit', array('id' => $groupId)));
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
		
			$data = new Data($app['dataFile']);
			$data->saveData($aData);

			$app['data'] = $data->fetchData();
		
			return $app->redirect($app['url_generator']->generate('groups'));
		
		})->bind('group-save');


		return $controllers;
    }
}
