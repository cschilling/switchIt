<?php
require(__DIR__.'/../../../config/prod.php');

if (!file_exists($dataFile))
{
	file_put_contents($logFile, date('Y-m-d H:i:s'." - Cronjob - DataFile not found in \"".$dataFile."\"\n"));
	die;
}

$aData = json_decode(file_get_contents($dataFile), true);
$time  = (int)(date('H') * 60) + (int)(date('i'));

foreach($aData['cronjobs'] AS $cKey => $cron)
{
	if (substr($cron['days'], date('N') -1, 1) == 1 && $time == $cron['time'])
	{
		file_put_contents($logFile, date('Y-m-d H:i:s'." - Cronjob - executing Cronjob #".$cKey." \"(".$cron['name'].")\"\n"));

		$aToSwitch = array();
		foreach($cron['switches'] AS $switchKey => $action)
		{
			$switch = $aData['switches'][$switchKey];

			$aToSwitch[] = $switch['config'].' '.(int)$switch['number'].' '.(int)$action;
		}

		if (file_put_contents($switchFile, json_encode($aToSwitch)))
			return true;
		else
			file_put_contents($logFile, date('Y-m-d H:i:s'." - Cronjob - could not write to file \"".$switchFile."\"\n"));
	}
}
