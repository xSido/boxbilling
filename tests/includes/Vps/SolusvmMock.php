<?php
class Vps_SolusvmMock
{
    public function __construct($c)
    {
        if (!isset($c["id"])) {
            throw new Exception("API ID is missing");
        }

        if (!isset($c["key"])) {
            throw new Exception("API key is missing");
        }

        if (!isset($c["ipaddress"])) {
            throw new Exception("API ip address is missing");
        }
    }

    public function node_virtualservers()
    {
        return [];
    }

    public function vserver_reboot()
    {
    }

    public function vserver_boot()
    {
    }

    public function vserver_shutdown()
    {
    }

    public function vserver_status()
    {
        return ["statusmsg" => "online"];
    }

    public function vserver_infoall()
    {
        return [];
    }

    public function vserver_rebuild()
    {
        return true;
    }

    public function listtemplates()
    {
        return [
            "templates" => "t1,t2",
        ];
    }

    public function listnodegroups()
    {
        return [];
    }

    public function listnodes()
    {
        return [
            "nodes" => "node1,node2",
        ];
    }

    public function listplans()
    {
        return [
            "plans" => "plan1,plan2",
        ];
    }

    public function vserver_create()
    {
        return [
            "vserverid" => "999",
            "virtid" => "101",
            "nodeid" => "4",
            "hostname" => "test.com",
            "rootpassword" => "rootpass",
            "consoleuser" => "user",
            "consolepassword" => "pass",
            "mainipaddress" => "12.12.12.12",
            "mainipaddress" => "12.12.12.12",
        ];
    }

    public function vserver_rootpassword($type)
    {
        return true;
    }

    public function vserver_hostname()
    {
        return true;
    }

    public function vserver_suspend()
    {
        return true;
    }

    public function vserver_unsuspend()
    {
        return true;
    }

    public function vserver_terminate()
    {
        return true;
    }

    public function vserver_change()
    {
        return true;
    }

    public function vserver_addip()
    {
        return true;
    }

    public function vserver_network_disable()
    {
        return true;
    }

    public function vserver_network_enable()
    {
        return true;
    }

    public function vserver_tun_disable()
    {
        return true;
    }

    public function vserver_tun_enable()
    {
        return true;
    }

    public function vserver_pae()
    {
        return true;
    }

    public function client_updatepassword()
    {
        return true;
    }

    public function client_list()
    {
        return [];
    }

    public function client_checkexists()
    {
        return false;
    }
}
