<?php
	require __DIR__ . '/init.php';

	$instances = get_proxmox_data();

	foreach ($instances as $instance) {
		if($instance->template) continue;

		$tmp['{#VMID}'] = $instance->vmid;
		$tmp['{#HOSTNAME}'] = $instance->name;

		$discovery_tmp['data'][] = $tmp;
	}

	$discovery_data = json_encode($discovery_tmp);

	$zabbix_sender
		->addData(getenv('ZABBIX_HOSTNAME'), 'proxmox.discovery', $discovery_data)
		->send();

	var_dump($zabbix_sender->getResponse());