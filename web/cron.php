<?php
	include("switch.php");
	
	$aData = json_decode(file_get_contents("data.json"), true);
	$time  = (date('H') * 60) + (date('i'));

	foreach($aData['cronjobs'] AS $cron)
	{
		if ($time == $cron['time'])
		{
			foreach($cron['switches'] AS $switchKey => $action)
			{
				$switch = $aData['switches'][$switchKey];
			
				// switch it!
				do_switch($switch['config'], $switch['number'], $action, $aData['options']['delay'], $aData['options']['server'], $aData['options']['port']);
			}
		}
	}
