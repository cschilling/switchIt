#!/usr/bin/php

<?php
require(__DIR__.'/../../../config/prod.php');

//the main process
while(true)
{
	clearstatcache();

	// check if file_exists and has been modified in <30 sec.
	if (file_exists($switchFile) && (date("U") - filemtime($switchFile)) < 20)
		$aToSwitch = json_decode(file_get_contents($switchFile), true);
	else
		$aToSwitch = array();

	if (sizeof($aToSwitch))
	{
		foreach($aToSwitch AS $switch)
		{
			shell_exec($sendBin.' '.$switch);

			usleep(500000);
		}

		// empty switchFile
		file_put_contents($switchFile, json_encode(array()));
	}

	usleep(50000);
}
