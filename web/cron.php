<?php
	$aData = json_decode(file_get_contents(__DIR__."/data.json"), true);
	$time  = (date('H') * 60) + (date('i'));

	foreach($aData['cronjobs'] AS $cKey => $cron)
	{
		if (substr($cron['days'], date('N') -1, 1) == 1 && $time == $cron['time'])
		{
			echo "excuting cronjob #".$cKey."\n";

			$aToSwitch = array();
			foreach($cron['switches'] AS $switchKey => $action)
			{
				$switch = $aData['switches'][$switchKey];
				
				$aToSwitch[] = $switch['config'].' '.(int)$switch['number'].' '.(int)$action;
			}

			if (file_put_contents('switch.json', json_encode($aToSwitch)))
				return true;
			else
				return $app['i18n']['errors']['switches_not_set'];
		}
	}
