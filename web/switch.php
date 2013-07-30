<?php
	function do_switch($group, $switch, $action = true, $delay = 0, $server = '127.0.0.1', $port = '11337')
	{
		$output = $group.str_pad($switch, 2, '0', STR_PAD_LEFT).(int)$action.(int)$delay;
		$ip		= (isset($_SERVER['SERVER_ADDR']))? $_SERVER['SERVER_ADDR']:$server;

		if (strlen($output) >= 8) {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create socket\n");
			socket_bind($socket, $ip) or die("Could not bind to socket\n");
			socket_connect($socket, $server, $port) or die("Could not connect to socket\n");
			socket_write($socket, $output, strlen ($output)) or die("Could not write output\n");
			socket_close($socket);
		}
	}