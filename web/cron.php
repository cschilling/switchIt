<?php
	include(__DIR__."/switch.php");
	
	$aData = json_decode(file_get_contents(__DIR__."/data.json"), true);
	$time  = (date('H') * 60) + (date('i'));

	foreach($aData['cronjobs'] AS $cKey => $cron)
	{
		if (substr($cron['days'], date('N') -1, 1) == 1 && $time == $cron['time'])
		{
			echo "excuting cronjob #".$cKey."\n";
			foreach($cron['switches'] AS $switchKey => $action)
			{
				$switch = $aData['switches'][$switchKey];
			
				// switch it!
				do_switch($switch['config'], $switch['number'], $action, $aData['options']['delay'], $aData['options']['server'], $aData['options']['port']);
			}
		}
	}
