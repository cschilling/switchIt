#!/usr/bin/php

<?php
require(__DIR__.'/../../../config/prod.php');

//the main process
while(true)
{
	clearstatcache();

	// check if data-file exists and has been modified in <30 sec.
	if (file_exists($switchFile) && (date("U") - filemtime($switchFile)) < 20)
		$aToSwitch = json_decode(file_get_contents($switchFile), true);
	else
		$aToSwitch = array();

    // check if switch-state file exists
	if (file_exists($switchStateFile))
		$aSwitchStates = json_decode(file_get_contents($switchStateFile), true);
	else
		$aSwitchStates = array();

	if (sizeof($aToSwitch))
	{
		foreach($aToSwitch AS $switch)
		{
            // check if a switch should be flipped
            if (substr($switch, -1) == '2')
            {
                $switchId = substr($switch, 0, -2);

                if (isset($aSwitchStates[$switchId]))
                    $state = (int)$aSwitchStates[$switchId];
                else
                    $state = 0;     // assume the switch is off by default

                // flip current state
                $state  = ($state)? 0:1;
                $switch = $switchId.' '.$state;
            }

            // set new switch-state
            shell_exec($sendBin.' '.$switch);

            $aSwitchStates[substr($switch, 0, -2)] = substr($switch, -1);

			usleep(500000);
		}

		// empty switchFile
		file_put_contents($switchFile, json_encode(array()));

        // save new switch-states
		file_put_contents($switchStateFile, json_encode($aSwitchStates));
	}

	usleep(50000);
}
