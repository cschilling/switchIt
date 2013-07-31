#!/usr/bin/php

<?php

$sendBin   = '/opt/raspberry-remote/send';
$dataFile  = __DIR__.'/switch.json';
$log       = '/var/log/switchItD.log';

/**
 * Method for displaying the help and default variables.
 **/
function displayUsage()
{
    global $log;
 
    echo "n";
    echo "Process for demonstrating a PHP daemon.n";
    echo "n";
    echo "Usage:n";
    echo "tDaemon.php [options]n";
    echo "n";
    echo "toptions:n";
    echo "tt--help display this help messagen";
    echo "tt--log=<filename> The location of the log file (default '$log')n";
    echo "n";
}

//configure command line arguments
if ($argc > 0)
{
	foreach($argv as $arg)
	{
		$args = explode('=',$arg);
		switch($args[0])
		{
			case '--help':
				return displayUsage();
			case '--log':
				$log = $args[1];
				break;
		}
	}
}

//the main process
while(true)
{
	if (file_exists($dataFile))
		$aToSwitch = json_decode(file_get_contents($dataFile), true);
	else
		$aToSwitch = array();

	if (sizeof($aToSwitch))
	{
		foreach($aToSwitch AS $switch)
		{
			file_put_contents($log, date('Y-m-d H:i:s').' - '.shell_exec('.'.$sendBin.' '.$switch), FILE_APPEND);

			usleep(500000);
		}

		file_put_contents($dataFile, json_encode(array()));
	}

	usleep(500000);
}