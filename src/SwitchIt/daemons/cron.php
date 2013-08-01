<?php
require(__DIR__.'/../../../config/prod.php');

if (!file_exists($dataFile))
{
	file_put_contents($logFile, date('Y-m-d H:i:s'." - Cronjob - DataFile not found in \"".$dataFile."\"\n"));
	die;
}

$aData   = json_decode(file_get_contents($dataFile), true);
$time    = (int)(date('H') * 60) + (int)(date('i'));



// set timezone if given
if (isset($aData) && isset($aData['options']) && isset($aData['options']['location']) &&  isset($aData['options']['location']['timezone']))
{
	date_default_timezone_set($aData['options']['location']['timezone']);

	$sunrise = date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP , $aData['options']['location']['lat'], $aData['options']['location']['lng'], 96);
	$sunset  = date_sunset (time(), SUNFUNCS_RET_TIMESTAMP , $aData['options']['location']['lat'], $aData['options']['location']['lng'], 96);

	// to minutes
	$sunrise = (int)(date('H', $sunrise) * 60) + (int)(date('i', $sunrise));
	$sunset  = (int)(date('H', $sunset)  * 60) + (int)(date('i', $sunset));
}
else
{
	$sunrise = false;
	$sunset  = false;
}

foreach($aData['cronjobs'] AS $cKey => $cron)
{
	$run = false;

	// if weekday does not match => continue to the next cronjob
	if (substr($cron['days'], date('N') -1, 1) <> 1)
		continue;

	switch($cron['type'])
	{
		case 0:
			if ($time == $cron['time'])
				$run = true;
			break;

		case 1:
			if ($sunrise !== false && $sunrise == $cron['time'])
				$run = true;
			break;

		case 2:
			if ($sunset !== false && $sunset == $cron['time'])
				$run = true;
	}

	if ($run)
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
