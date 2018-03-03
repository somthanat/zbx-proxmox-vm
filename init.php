<?php
	require __DIR__ . '/vendor/autoload.php';

	$dotenv = new Dotenv\Dotenv(__DIR__, 'config');
	$dotenv->load();

	$zabbix_sender = new Disc\Zabbix\Sender(getenv('ZABBIX_SERVER'), (int)getenv('ZABBIX_SERVER_PORT'));

	function get_proxmox_data()
	{
		if(getenv('CACHE_ENABLE'))
		{
			if(file_exists(getenv('CACHE_FILE')))
			{
				$last_update = filemtime(getenv('CACHE_FILE'));

				if(time()-20 < $last_update)
				{
					return json_decode(file_get_contents(getenv('CACHE_FILE')));					
				}
			}
		}

		$guzzle = new GuzzleHttp\Client([
			'base_uri' => rtrim(getenv('PROXMOX_API_URL'), '/') . '/',
			'timeout'  => 3.0,
		]);

		$response = $guzzle->request('POST', 'access/ticket', [
			'body' => http_build_query([
				'username' => getenv('PROXMOX_USERNAME'),
				'password' => getenv('PROXMOX_PASSWORD')
			])
		]);

		if($response->getStatusCode()==200)
		{
			$data       = json_decode($response->getBody())->data;
			$csrf_token = $data->CSRFPreventionToken;
			$ticket     = $data->ticket;
		} else {
			die("ERROR #1");
		}

		$response = $guzzle->request('GET', 'cluster/resources?type=vm', [
			'headers' => [
				'Cookie' => 'PVEAuthCookie='.urlencode($ticket),
				'CSRFPreventionToken' => $csrf_token
			]
		]);

		if($response->getStatusCode()==200)
		{
		    $data = json_decode($response->getBody())->data;
		} else {
			die("ERROR #2");
		}

		if(getenv('CACHE_ENABLE'))
		{
			file_put_contents(getenv('CACHE_FILE'), json_encode($data));
		}

		return $data;
	}