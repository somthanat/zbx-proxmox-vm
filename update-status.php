<?php
    require __DIR__ . '/init.php';

    $instances = get_proxmox_data();

    foreach ($instances as $instance) {
        if($instance->template) continue;

        $hostname = getenv('ZABBIX_HOSTNAME');
        $vmid     = $instance->vmid;
        
        $zabbix_sender->addData($hostname, "proxmox.vm.cpu[$vmid]", number_format($instance->cpu*100, 2));        
        $zabbix_sender->addData($hostname, "proxmox.vm.available_memory[$vmid]", $instance->maxmem-$instance->mem);
        $zabbix_sender->addData($hostname, "proxmox.vm.memory_usage[$vmid]", $instance->mem);
        $zabbix_sender->addData($hostname, "proxmox.vm.status[$vmid]", ($instance->status=='running')?1:0);
        $zabbix_sender->addData($hostname, "proxmox.vm.uptime[$vmid]", $instance->uptime);
        $zabbix_sender->addData($hostname, "proxmox.vm.diskread[$vmid]", $instance->diskread);
        $zabbix_sender->addData($hostname, "proxmox.vm.diskwrite[$vmid]", $instance->diskwrite);
        $zabbix_sender->addData($hostname, "proxmox.vm.netin[$vmid]", $instance->netin);
        $zabbix_sender->addData($hostname, "proxmox.vm.netout[$vmid]", $instance->netout);

        $zabbix_sender->addData($hostname, "proxmox.vm.node[$vmid]", $instance->node);
        $zabbix_sender->addData($hostname, "proxmox.vm.cpu_core[$vmid]", $instance->maxcpu);
        $zabbix_sender->addData($hostname, "proxmox.vm.memory[$vmid]", $instance->maxmem);
        $zabbix_sender->addData($hostname, "proxmox.vm.storage[$vmid]", $instance->maxdisk);

        $zabbix_sender->send();
    }