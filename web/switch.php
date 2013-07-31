<?php

class switchIt
{
	private $socket = null;

	private function connect($server = '127.0.0.1', $port = '11337')
	{
		if (is_null($this->socket) || stream_get_meta_data($this->socket))
		{
			$ip           = (isset($_SERVER['SERVER_ADDR']))? $_SERVER['SERVER_ADDR']:$server;

			$this->socket = pfsockopen($server, $port, $errno, $errstr); 
			
			if (!$this->socket)
				return "Could not create socket";

			if (!socket_bind($this->socket, $ip))
				return "Could not bind to socket";

			if (!socket_connect($this->socket, $server, $port))
				return "Could not connect to socket";

			return true;
		}
		else
			return true;
	}

	public function doSwitch($group, $switch, $action = true, $delay = 0, $server = '127.0.0.1', $port = '11337')
	{
		$output = $group.str_pad($switch, 2, '0', STR_PAD_LEFT).(int)$action.(int)$delay;

		if (is_null($this->socket))
		{
			$result = $this->connect($server, $port);

			// error?
			if ($result !== true)
				return $result;
		}

		if (!socket_write($socket, $output, strlen($output)))
			return "Could not write output\n";

		return true;
	}
}
