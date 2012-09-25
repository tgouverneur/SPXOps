<?php

class OSAix extends OSType
{
  public static $binPaths = array(
    "/bin",
    "/usr/bin",
    "/usr/local/bin",
    "/sbin",
    "/usr/sbin",
    "/usr/local/sbin",
  );

  protected static $_update = array(
  //  "update_uname",
  //  "update_release",
  //  "update_network",
  //  "update_hostid",
  //  "update_nfs_shares",
  //  "update_nfs_mount",
  );

  /* Updates function for AIX */
  
  /**
   * hostid
   */
  public static function update_hostid(&$s) {

    /* get hostid */
    $hostid = $s->findBin('hostid');

    $cmd_hostid = "$hostid";
    $out_hostid = $s->exec($cmd_hostid);

    if ($s->data('os:hostid') != $out_hostid) {
      $s->setData('os:hostid', $out_hostid);
      $s->log('os:hostid => '.$out_hostid, LLOG_INFO);
    }

    return 0;
  }

  /**
   * network
   */
  public static function update_network(&$s) {

    $ip = $s->findBin('ip');
    $cmd_ip = "$ip addr";
    $out_ip = $s->exec($cmd_ip);

    $lines = explode(PHP_EOL, $out_ip);

    $found_if = array();
    $c_if = null;

    foreach($lines as $line) {
      $vnet = null;
      $pnet = null;
      $line = trim($line);
      if (empty($line))
	continue;
      
      if (preg_match('/^[0-9]*: ([a-z0-9]*): ([A-Z,_<>]*)/', $line, $m)) {
        $pnet = new Net();
	$pnet->fk_server = $s->id;
	$pnet->layer = 2; // ether
        $pnet->ifname = $m[1];
	if ($pnet->fetchFromFields(array('layer', 'ifname', 'fk_server'))) {
          $pnet->insert();
	  $s->log("Added $pnet to server", LLOG_INFO);
	  $s->a_net[] = $pnet;
	}
        if (strcmp($pnet->flags, $m[2])) {
          $pnet->flags = $m[2];
	  $s->log("Updated flags for $pnet to be ".$pnet->flags, LLOG_DEBUG);
	  $pnet->update();
	}
        $c_if = $pnet;
	$found_if[''.$c_if] = $c_if;
      } else if (preg_match('/^link\/ether/', $line)) {
        $f_eth = explode(' ', $line);
	if (strcmp($c_if->address, $f_eth[1])) {
	  $c_if->address = $f_eth[1];
	  $s->log("Updated layer 2 address for $c_if to be ".$c_if->address, LLOG_DEBUG);
	  $c_if->update();
	  $found_if[''.$c_if] = $c_if;
	}

      } else if (preg_match('/^inet ([0-9\.\/]*) /', $line, $m)) {
        $f_eth = explode(' ', $line);
        $vnet = new Net();
        $vnet->ifname = $c_if->ifname;
	$vnet->fk_server = $s->id;
	$vnet->layer = 3; /* IP */
	$ipaddr = explode('/', $m[1]);
        $vnet->address = $ipaddr[0];
        $vnet->netmask = $ipaddr[1];
	if ($vnet->fetchFromFields(array('ifname', 'version', 'fk_server', 'layer', 'address', 'netmask'))) {
	  $vnet->insert();
	  $s->log("Added alias $vnet to server", LLOG_INFO);
	  $s->a_net[] = $vnet;
	}
	if ($f_eth[count($f_eth) - 2] == 'secondary') {
          $alias = explode(':', $f_eth[count($f_eth) - 1], 2);
	  if (count($alias) == 2) {
	    if (strcmp($vnet->alias, $alias[1])) {
	      $vnet->alias = $alias[1];
              $s->log("Updated alias for $vnet to be ".$vnet->alias, LLOG_DEBUG);
	      $vnet->update();
	    }
	  }
	}
      
      } else if (preg_match('/^inet6 ([0-9a-z:\/]*) /', $line, $m)) {
        $f_eth = explode(' ', $line);
        $vnet = new Net();
        $vnet->ifname = $c_if->ifname;
        $vnet->fk_server = $s->id;
        $vnet->layer = 3; /* IP */
        $vnet->version = 6; /* v6 */
        $ipaddr = explode('/', $m[1]);
        $vnet->address = $ipaddr[0]; 
        $vnet->netmask = $ipaddr[1];
        if ($vnet->fetchFromFields(array('ifname', 'version', 'fk_server', 'layer', 'address', 'netmask'))) {
          $vnet->insert();
          $s->log("Added alias6 $vnet to server", LLOG_INFO);
	  $s->a_net[] = $vnet;
        }
        if ($f_eth[count($f_eth) - 2] == 'secondary') {
          $alias = explode(':', $f_eth[count($f_eth) - 1], 2);
          if (count($alias) == 2) {
            if (strcmp($vnet->alias, $alias[1])) {
              $vnet->alias = $alias[1];
              $s->log("Updated alias6 for $vnet to be ".$vnet->alias, LLOG_DEBUG);
              $vnet->update();
            }
          }
        }
      }
      $found_if[''.$vnet] = $vnet;
    }

    foreach($s->a_net as $n) {
      if (isset($found_if[''.$n])) {
        continue;
      }
      $s->log("Removing net $n", LLOG_INFO);
      $n->delete();
    }

    /* default router */
    
    $cmd_ip = "$ip ro";
    $out_ip = $s->exec($cmd_ip);

    $lines = explode(PHP_EOL, $out_ip);
    $defrouter = null;

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line))
        continue;

      $f = preg_split("/\s+/", $line);

      if (!strcmp($f[0], 'default')) {
	if (!strcmp($f[1], 'via')) {
          $defrouter = $f[2];
	}
	break;
      }
    }

    if ($defrouter &&
	strcmp($s->data('net:defrouter'), $defrouter)) {
      $s->setData('net:defrouter', $defrouter);
      $s->log("Change defrouter => $defrouter", LLOG_INFO);
    }

    return 0;
  }
  /**
   * uname
   */
  public static function update_uname(&$s) {

    /* get uname -a */
    $uname = $s->findBin('uname');

    $cmd_uname = "$uname -r";
    $out_uname = $s->exec($cmd_uname);
    $kr_version = $out_uname;
    
    $cmd_uname = "$uname -i";
    $out_uname = $s->exec($cmd_uname);
    $hw_class = $out_uname;

    $cmd_uname = "$uname -p";
    $out_uname = $s->exec($cmd_uname);
    $platform = $out_uname;

    $cmd_uname = "$uname -m";
    $out_uname = $s->exec($cmd_uname);
    $cputype = $out_uname;

    if ($s->data('os:kernel') != $kr_version) {
      $s->setData('os:kernel', $kr_version);
      $s->log('os:kernel => '.$kr_version, LLOG_INFO);
    }
    if ($s->data('hw:class') != $hw_class) {
      $s->setData('hw:class', $hw_class);
      $s->log('hw:class => '.$hw_class, LLOG_INFO);
    }
    if ($s->data('hw:platform') != $platform) {
      $s->setData('hw:platform', $platform);
      $s->log('hw:platform => '.$platform, LLOG_INFO);
    }

    return 0;
  }


  /* Screening */
  public static function htmlDump($s) {

/*
    $version = $s->data('linux:version'); 
    $ver_name = $s->data('linux:ver_name');
    $version = "$version ($ver_name)";

    return array(
                'Distribution' => $s->data('linux:name'),
                'Version' => $version,
		'Kernel' => $s->data('os:kernel'),
           );
*/
    return array();
  }

  public static function dump($s) {

/*
    $distro = $s->data('linux:name');
    $version = $s->data('linux:version');
    $ver_name = $s->data('linux:ver_name');
    $ker_ver = $s->data('os:kernel');
    if (empty($distro)) $distro = null;
    if (empty($version)) $version = null;
    if (empty($ver_name)) $ver_name = null;
    if (empty($ker_ver)) $ker_ver = null;
    $txt = '';
    $txt .= $s->o_os->name.' ';
    $txt .= ($ker_ver)?('- '.$ker_ver.' '):'';
    $txt .= ($distro)?('/ '.$distro.' '):'';
    $txt .= ($version)?('/ '.$version.' '):'';
    $txt .= ($ver_name)?('( '.$ver_name.') '):'';

    $s->log(sprintf("%15s: %s", 'OS', $txt), LLOG_INFO);
*/
  }

}

?>
