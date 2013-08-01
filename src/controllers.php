<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SwitchIt\Data;
use SwitchIt\controller AS c;

// load data
$data              = new Data($dataFile);
$app['data']       = $data->fetchData();
$app['dataFile']   = $dataFile;
$app['switchFile'] = $switchFile;

// load i18n
$app['i18n']  = json_decode(file_get_contents(__DIR__.'/../i18n/'.$app['data']['options']['locale'].'.json'), true);

// flash-messages
$app->before(function() use ($app) {
    $flash = $app['session']->get('flash');
    $app['session']->set('flash', null);

    if (!empty( $flash ))
		$app['twig']->addGlobal('flash', $flash);
});


$app->mount('/',         new c\ContentControllerProvider());
$app->mount('/switch',   new c\SwitchControllerProvider());
$app->mount('/group',    new c\GroupControllerProvider());
$app->mount('/cronjob',  new c\CronjobControllerProvider());
$app->mount('/settings', new c\SettingsControllerProvider());


$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $page = 404 == $code ? '404.html' : '500.html';

    return new Response($app['twig']->render($page, array('code' => $code)), $code);
});
