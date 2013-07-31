<?php

class switchIt
{
	private $socket = false;

	public function connect($server = '127.0.0.1', $port = '11337')
	{
		if ($this->socket === false)
		{
			$ip           = (isset($_SERVER['SERVER_ADDR']))? $_SERVER['SERVER_ADDR']:$server;
			$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

			if ($this->socket === false)
			{
				$errorcode = socket_last_error();
				$errormsg  = socket_strerror($errorcode);

				return "Could not create socket: [$errorcode] $errormsg";
			}

			if (!@socket_bind($this->socket, $ip))
				return "Could not bind to socket";

			if (!@socket_connect($this->socket, $server, $port))
				return "Could not connect to socket";

			return true;
		}
		else
			return true;
	}

	public function doSwitch($group, $switch, $action = true, $delay = 0)
	{
		$output = $group.str_pad($switch, 2, '0', STR_PAD_LEFT).(int)$action;

		file_put_contents('switch.json', $group.' '.$switch.' '.(int)$action, FILE_APPEND);

		/*
		if ($this->socket === false)
			return "not connected";

		$result = socket_write($this->socket, $output, strlen($output));

		if ($result < 1)
			return "Could not write output";

		usleep(60000);

		file_put_contents('switch.log', "awake again\n", FILE_APPEND);
		*/

		return true;
	}

	public function disconnect()
	{
		socket_close($this->socket);

		$this->socket = false;

		return true;
	}
}
