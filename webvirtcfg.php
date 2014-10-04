<?php include '/usr/local/emhttp/plugins/webvirtmgr/webvirtmgr.css';?>
<?php
# -------------------------------------------------------------------------
## Load current config file and check if program is installed already
# -------------------------------------------------------------------------

# This will clean any ^M characters caused by windows from the config file before use
if (file_exists("/boot/config/plugins/webvirtmgr/webvirtmgr.cfg"))
	shell_exec("sed -i 's!\r!!g' '/boot/config/plugins/webvirtmgr/webvirtmgr.cfg'");
$network_cfg = parse_ini_file( "/boot/config/network.cfg" );
$webvirtmgr_cfg = parse_ini_file( "/boot/config/plugins/webvirtmgr/webvirtmgr.cfg" );
$webvirtmgr_installed = file_exists( $webvirtmgr_cfg["INSTALLDIR"] . "/manage.py" ) ? "yes" : "no";

# -------------------------------------------------------------------------
## Collect local variables from config files and verify data as best as possible
# -------------------------------------------------------------------------

# Service Status Variable
if (isset($webvirtmgr_cfg['SERVICE']) && ($webvirtmgr_cfg['SERVICE'] == "enable" || $webvirtmgr_cfg['SERVICE'] == "disable"))
	$webvirtmgr_service = $webvirtmgr_cfg['SERVICE'];
else
	$webvirtmgr_service = "disable";

# Install Directory Variable
if (isset($webvirtmgr_cfg['INSTALLDIR']))
	$webvirtmgr_installdir = $webvirtmgr_cfg['INSTALLDIR'];
else
	$webvirtmgr_installdir = "/usr/local/webvirtmgr";

# Port Number Variable
if (isset($webvirtmgr_cfg['PORT']) && is_numeric($webvirtmgr_cfg['PORT'])) {
	$webvirtmgr_port = $webvirtmgr_cfg['PORT'];
	if ($webvirtmgr_port < 0 || $webvirtmgr_port > 65535)
		$webvirtmgr_port = "8000";
} else {
	$webvirtmgr_port = "8000";
}

# Run As User Variable
if (isset($webvirtmgr_cfg['RUNAS']))
	$webvirtmgr_runas = $webvirtmgr_cfg['RUNAS'];
else
	$webvirtmgr_runas = "nobody";

# Username Check Status Variable
if (isset($webvirtmgr_cfg['USERNAME']))
	$webvirtmgr_username = $webvirtmgr_cfg['USERNAME'];
else
	$webvirtmgr_username = "";

# Password Check Status Variable
if (isset($webvirtmgr_cfg['PASSWORD']))
	$webvirtmgr_password = $webvirtmgr_cfg['PASSWORD'];
else
	$webvirtmgr_password = "";

# -------------------------------------------------------------------------
## Check is program is installed and running to get extra information
# -------------------------------------------------------------------------
if ($webvirtmgr_installed=="yes") {
	$webvirtmgr_pids = shell_exec ("pgrep -f manage.py" );
	if ($webvirtmgr_pids == "")
		$webvirtmgr_running = "no";
	else
		$webvirtmgr_running = "yes";
	if ($webvirtmgr_running == "yes")
		$webvirtmgr_updatestatus = "Running";
	else
		$webvirtmgr_updatestatus = "Stopped";

	$webvirtmgr_storagesize = shell_exec ( "/etc/rc.d/rc.webvirtmgr storagesize" );
	$webvirtmgr_datacheck = shell_exec ( "/etc/rc.d/rc.webvirtmgr datacheck" );

	# Get online status of the program
	$webvirtmgr_gitmsg = shell_exec ( "git --git-dir=$webvirtmgr_installdir/.git --work-tree=$webvirtmgr_installdir status --untracked-files=no | grep  \"commits\" | sed -e 's/^# Your branch/WebVirtMgr/'" );
	if ($webvirtmgr_gitmsg == "")
		$webvirtmgr_gitstatus = "current";
	else
		$webvirtmgr_gitstatus = "update";

	# Get current installed version of the program
	$webvirtmgr_curversion = trim ( shell_exec ( "git --git-dir=$webvirtmgr_installdir/.git --work-tree=$webvirtmgr_installdir describe --tags" ) );
	if ($webvirtmgr_curversion == "")
		$webvirtmgr_curversion = "couldn't determine the WebVirtMgr version";

	# Get usernames from webvirtmgr database 
	$webvirtmgr_database = rtrim ( shell_exec ( "sqlite3 $webvirtmgr_installdir/webvirtmgr.sqlite3 'select username from auth_user' | awk '{printf \"%s,\",$1,$2 }'" ) , "," );
	if ($webvirtmgr_database != "") 	
		$webvirtmgr_userarray = explode ( "," , "$webvirtmgr_database" );
}
?>