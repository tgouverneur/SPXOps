<?php

class OSSolaris extends OSType
{
  public static $binPaths = array(
    "/bin",
    "/usr/bin",
    "/usr/local/bin",
    "/sbin",
    "/usr/sbin",
    "/usr/local/sbin",
    "/opt/csw/bin",
    "/opt/csw/sbin",
  );

  protected static $_update = array(
    "update_uname",
    "update_prtdiag",
    "update_prtconf",
    "update_release",
    "update_sneep",
    "update_network",
    "update_cpu",
    "update_hostid",
    "update_zones",
    "update_patches",
    "update_packages",
    "update_nfs_shares",
    "update_nfs_mount",
    "update_projects",
    "update_disk",
    "update_fcinfo",
    "update_zfs",
    "update_sds",
    "update_cdp",
//    "update_swap",
  );

  /* Updates function for Solaris */

  /**
   * nfs_shares
   */
  public static function update_nfs_shares(&$s) {

    $cat = $s->findBin('cat');
    $cmd_cat = "$cat /etc/dfs/sharetab";
    $out_cat = $s->exec($cmd_cat);

    $lines = explode(PHP_EOL, $out_cat);
    $found_n = array();

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }

      $f = preg_split("/\s+/", $line);
      if (count($f) < 4) {
        continue; // Malformed line
      }

      if (strcmp($f[2], 'nfs')) {
        continue; // not nfs
      }
      
      $no = new NFS();
      $no->type = 'share';
      $no->fk_server = $s->id;
      $no->share = $f[0];
      $changed = false;
      if ($no->fetchFromFields(array('type', 'fk_server', 'share'))) {
        $no->insert();
        $s->log("Added $no", LLOG_INFO);
        $s->a_nfss[] = $no;
      }
      if (strcmp($no->acl, $f[3])) {
        $no->acl = $f[3];
        $s->log("Changed acl of $no to be ".$no->acl, LLOG_INFO);
	$changed = true;
      }
      $df = $s->findBin('df');
      $cmd_df = "$df -k ".$no->share;
      $out_df = $s->exec($cmd_df);
     
      $lines_df = explode(PHP_EOL, $out_df);
      if (count($lines_df) == 2) {
        $line_df = $lines_df[1];
        $f_df = preg_split("/\s+/", $line_df);
        if ($no->size != $f_df[1]) {
          $no->size = $f_df[1];
	  $changed = true;
          $s->log("Changed size of $no to be ".$no->size, LLOG_INFO);
        }
        if ($no->used != $f_df[2]) {
          $no->used = $f_df[2];
	  $changed = true;
          $s->log("Changed used of $no to be ".$no->size, LLOG_INFO);
        }
      }
      if ($changed) $no->update();
      $found_n[''.$no] = $no;
    }

    foreach($s->a_nfss as $ns) {
      if (isset($found_n[''.$ns])) {
        continue;
      }
      $s->log("Removing NFS $ns", LLOG_INFO);
      $ns->delete();
    }

    return 0;
  }

  /**
   * nfs_mount
   */
  public static function update_nfs_mount(&$s) {

    $cat = $s->findBin('cat');
    $cmd_cat = "$cat /etc/mnttab";
    $out_cat = $s->exec($cmd_cat);

    $lines = explode(PHP_EOL, $out_cat);
    $found_n = array();

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }

      $f = preg_split("/\s+/", $line);
      if (count($f) < 5) {
        continue; // Malformed line
      }

      if (strcmp($f[2], 'nfs')) {
        continue; // not nfs
      }
      
      if (!strcmp($f[1], '/vol')) {
        continue; // skip /vol in some s9 systems
      }

      $no = new NFS();
      $no->type = 'mount';
      $no->fk_server = $s->id;
      $no->path = $f[1];
      $changed = false;
      if ($no->fetchFromFields(array('type', 'fk_server', 'path'))) {
        $no->insert();
        $s->log("Added $no", LLOG_INFO);
        $s->a_nfsm[] = $no;
      }
      $remote_f = explode(':', $f[0]);
      if (strcmp($no->share, $remote_f[1])) {
        $no->share = $remote_f[1];
        $s->log("Changed share of $no to be ".$no->share, LLOG_INFO);
	$changed = true;
      }
      if (strcmp($no->dest, $remote_f[0])) {
        $no->dest = $remote_f[0];
        $s->log("Changed dest of $no to be ".$no->dest, LLOG_INFO);
	$changed = true;
      }
/*
 @TODO Fix DF for nfs mount inside zones
  if the flag zone=<zname> is present inside mnttab,
  the df should be taken with a zlogin
 */
      $df = $s->findBin('df');
      $sudo = $s->findBin('sudo');
      $cmd_df = "$sudo $df -k ".$no->path;
      $out_df = $s->exec($cmd_df);
     
      $lines_df = explode(PHP_EOL, $out_df);
      if (count($lines_df) == 2) {
        $line_df = $lines_df[1];
        $f_df = preg_split("/\s+/", $line_df);
        if ($no->size != $f_df[1]) {
          $no->size = $f_df[1];
	  $changed = true;
          $s->log("Changed size of $no to be ".$no->size, LLOG_INFO);
        }
        if ($no->used != $f_df[2]) {
          $no->used = $f_df[2];
	  $changed = true;
          $s->log("Changed used of $no to be ".$no->size, LLOG_INFO);
        }
      }
      if ($changed) $no->update();
      $found_n[''.$no] = $no;
    }

    foreach($s->a_nfsm as $ns) {
      if (isset($found_n[''.$ns])) {
        continue;
      }
      $s->log("Removing NFS $ns", LLOG_INFO);
      $ns->delete();
    }

    return 0;
  }

  public static function update_packages_s10(&$s) {

    $pkginfo = $s->findBin('pkginfo');
    $cmd_pkginfo = "$pkginfo -l";
    $out_pkginfo = $s->exec($cmd_pkginfo, null, 600); /* this command can take time */

    $lines = explode(PHP_EOL, $out_pkginfo);
    $found_p = array();
    
    $pkg = null;
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }
      $f = explode(':', $line, 2);
      if (count($f) != 2) {
	continue; /* Malformed or useless line */
      }
      switch ($f[0]) {
	case 'PKGINST':
          if ($pkg) {
	    $found_p[$pkg['name']] = $pkg;
	    $pkg = array();
	  }
	  $pkg['name'] = trim($f[1]);
	break;
	case 'NAME':
	  $pkg['lname'] = trim($f[1]);
	break;
	case 'CATEGORY':
	break;
	case 'ARCH':
	  $pkg['arch'] = trim($f[1]);
	break;
	case 'VERSION':
	  $pkg['version'] = trim($f[1]);
	break;
	case 'BASEDIR':
	  $pkg['basedir'] = trim($f[1]);
	break;
	case 'VENDOR':
	  $pkg['vendor'] = trim($f[1]);
	break;
	case 'DESC':
	  $pkg['desc'] = trim($f[1]);
	break;
	case 'PSTAMP':
	  $pkg['fmri'] = trim($f[1]);
	break;
	case 'INSTDATE':
	  /* @TODO: parse install date and fill it */
	break;
	case 'STATUS':
	  $pkg['status'] = trim($f[1]);
	break;
        default:
	break;
      }
    }
    if ($pkg) {
      $found_p[$pkg['name']] = $pkg;
    }
    return $found_p;
  }

  public static function update_packages_s11(&$s) {

    $pkg = $s->findBin('pkg');
    $cmd_pkg = "$pkg list -H -v";
    $out_pkg = $s->exec($cmd_pkg);

    $lines = explode(PHP_EOL, $out_pkg);
    $found_p = array();

    $pkg = null;
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }
      if (preg_match('/^pkg:\/\/([^\/]*)\/([^@]*)@([^,]*),([^-]*)-([^:]*):([^\s]*)/', $line, $pf)) {
        $pkg = array();
        $pkg['name'] = $pf[2];
        $pkg['vendor'] = $pf[1];
        $pkg['version'] = $pf[3];
        $pkg['status'] = 'installed';
        $pkg['fmri'] = $pf[3].','.$pf[4].'-'.$pf[5].':'.$pf[6];
        $found_p[$pf[2]] = $pkg;
      }
    }
    return $found_p;
  }

  /**
   * packages
   */
  public static function update_packages(&$s) {

    if ($s->data('os:major') > 10) {
      $found_p = OSSolaris::update_packages_s11($s);
    } else {
      $found_p = OSSolaris::update_packages_s10($s);
    }

    foreach ($found_p as $pkg) {
  
      $po = new Pkg();
      $po->name = $pkg['name'];
      $po->fk_server = $s->id;

      if ($po->fetchFromFields(array('name', 'fk_server'))) {
        $s->log('new package found: '.$po, LLOG_INFO);
        $po->insert();
        array_push($s->a_pkg, $po);
      }

      $f = array('lname', 'arch', 'version', 'basedir', 'vendor', 'desc', 'fmri', 'status');
      foreach($f as $field) {
        if (isset($pkg[$field]) && $pkg[$field] != $po->{$field}) {
          $po->{$field} = $pkg[$field];
	  $s->log("$po:$field => ".$pkg[$field], LLOG_DEBUG);
        }
      }
      $po->update();
    }

    foreach($s->a_pkg as $po) {
      if (isset($found_p[$po->name])) {
        continue;
      }
      $s->log("Removing package $po", LLOG_INFO);
      $po->delete();
    }
    return 0;
  }


  /**
   * patches
   */
  public static function update_patches(&$s) {

    if ($s->data('os:major') > 10) {
      return 0; /* no more patch with solaris > 10 */
    }

    $showrev = $s->findBin('showrev');
    $cmd_showrev = "$showrev -p";
    $out_showrev = $s->exec($cmd_showrev);

    $lines = explode(PHP_EOL, $out_showrev);
    $found_p = array();
    
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }

      $f = explode(' ', $line);
      $patch = $f[1];

      $po = new Patch();
      $po->patch = $patch;
      $po->fk_server = $s->id;
      $found_p[$po->patch] = $po;

      if ($po->fetchFromFields(array('patch', 'fk_server'))) {
        $s->log('new patch found: '.$po, LLOG_INFO);
        $po->insert();
        array_push($s->a_patch, $po);
      }
    }

    foreach($s->a_patch as $po) {
      if (isset($found_p[$po->patch])) {
        continue;
      }
      $s->log("Removing patch $po", LLOG_INFO);
      $po->delete();
    }
    return 0;
  }

  /**
   * zones
   */
  public static function update_zones(&$s) {

    /* get hostid */
    $zoneadm = $s->findBin('zoneadm');

    $cmd_zoneadm = "$zoneadm list -pc";
    $out_zoneadm = $s->exec($cmd_zoneadm);
    
    $lines = explode(PHP_EOL, $out_zoneadm);
    $found_z = array();

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue; 
      }
      $f = explode(':', $line);
      if ($f[1] == 'global') {
        continue;
      }
      $z = new Zone();
      $z->name = $f[1];
      $z->fk_server = $s->id;
      $u = 0;

      if ($z->fetchFromFields(array('name', 'fk_server'))) {
        $s->log('new zone registered: '.$z, LLOG_INFO);
        $z->insert();
        array_push($s->a_zone, $z);
      }
      if ($z->zoneid != $f[0]) {
        $z->zoneid = $f[0];
        $u++;
      }
      if ($z->status != $f[2]) {
        $z->status = $f[2];
	$u++;
      }
      if ($z->path != $f[3]) {
        $z->path = $f[3];
	$u++;
      }
      if ($z->brand != $f[5]) {
        $z->brand = $f[5];
	$u++;
      }
      if ($z->iptype != $f[6]) {
        $z->iptype = $f[6];
	$u++;
      }
      if ($u) {
        $s->log("Updated $u infos about zone $z", LLOG_INFO);
	$z->update();
      }
      $found_z[$z->name] = $z;
    }

    foreach($s->a_zone as $sz) {
      if (isset($found_z[$sz->name])) {
        continue;
      }
      $s->log("Removing zone $sz", LLOG_INFO);
      $sz->delete();
    }

    return 0;
  }

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
   * cpu info
   */
  public static function update_cpu(&$s) {

    $psrinfo = $s->findBin('psrinfo');
    $cmd_psrinfo = "$psrinfo -pv";
    $out_psrinfo = $s->exec($cmd_psrinfo);

    $lines = explode(PHP_EOL, $out_psrinfo);

    $nrcpu = $nrcore = $nrstrand = 0;

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) 
        continue;

      if (preg_match('/^The physical processor has ([0-9]*) cores and ([0-9]*) virtual processor.*/', $line, $cpu)) {
        $nrcpu++;
	$nrcore += $cpu[1];
	$nrstrand += $cpu[2];
      } else if (preg_match('/^The physical processor has ([0-9]*) virtual processor.*/', $line, $cpu)) {
        $nrcpu++;
        $nrcore += 1;
        $nrstrand += $cpu[1];
      }

    }

    if ($s->data('hw:nrcpu') != $nrcpu) {
      $s->setData('hw:nrcpu', $nrcpu);
      $s->log('Updated hw:nrcpu => '.$nrcpu, LLOG_INFO);
    }

    if ($s->data('hw:nrcore') != $nrcore) {
      $s->setData('hw:nrcore', $nrcore);
      $s->log('Updated hw:nrcore => '.$nrcore, LLOG_INFO);
    }

    if ($s->data('hw:nrstrand') != $nrstrand) {
      $s->setData('hw:nrstrand', $nrstrand);
      $s->log('Updated hw:nrstrand => '.$nrstrand, LLOG_INFO);
    }

    return 0;
  }

  /**
   * network
   */

  public static function update_network_s10(&$s) {

    $found_if = array();

    $sudo = $s->findBin('sudo');
    $ifconfig = $s->findBin('ifconfig');
    $cmd_ifconfig = "$sudo $ifconfig -a";
    $out_ifconfig = $s->exec($cmd_ifconfig);

    $lines = explode(PHP_EOL, $out_ifconfig);

    $ifname = $alias = null;
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line))
        continue;

      $f = preg_split("/\s+/", $line);

      if (preg_match('/^([a-z0-9:]*):$/', $f[0], $m)) {

        $ifname = $m[1];
	$alias = '';
	$flags = '';

        if (preg_match('/:/', $m[1])) {
          $ifname = explode(':', $m[1]);
	  $alias = $ifname[1];
          $ifname = $ifname[0];
        }
	if (preg_match('/flags=([0-9]*)<([A-Za-z0-9,]*)>/', $f[1], $m)) {
          $flags = $m[2];
        }
        if (empty($alias)) {
          // physical should match $ifname already
	  if (isset($found_if[$ifname])) {
            $c_if = $found_if[$ifname];
            if (!isset($found_if[$ifname]['flags']) ||
                empty($found_if[$ifname]['flags'])) {
              $found_if[$ifname]['flags'] = $flags;
            }

	  } else {
	    $if = array();
	    $if['ifname'] = $ifname;
	    $if['flags'] = $flags;
            $if['layer'] = 2;
            $if['fk_server'] = $s->id;
	    $found_if[$ifname] = $if;
	  }
        }

      } else if (!strcmp($f[0], 'ether')) {
        if (isset($found_if[$ifname])) {
          $found_if[$ifname]['address'] = $f[1];
        }
      } else if (!strcmp($f[0], 'inet') && strcmp($f[1], '0.0.0.0') && $f[1] != 0) {

        $vif = array();
	$vif['ifname'] = $ifname;
	$vif['alias'] = $alias;
	$vif['layer'] = 3;
        $vif['fk_server'] = $s->id;
	$vif['version'] = 4;
	$vif['address'] = $f[1];
        if (!strcmp($f[2], 'netmask')) {
	  $vif['netmask'] = $f[3];
	}
        $found_if[] = $vif;

      } else if (!strcmp($f[0], 'inet6') && strcmp($f[1], '::/0')) {

        $vif = array();
	$vif['ifname'] = $ifname;
	$vif['alias'] = $alias;
	$vif['layer'] = 3;
	$vif['version'] = 6;
	$vif['address'] = $f[1];
        $vif['fk_server'] = $s->id;
        if (preg_match('/\//', $vif['address'])) {
          $vif['address'] = explode('/', $vif['address']);
	  $vif['netmask'] = $vif['address'][1];
	  $vif['address'] = $vif['address'][0];
        }
        if (count($f) > 3 && !strcmp($f[2], 'netmask')) {
	  $vif['netmask'] = $f[3];
	}
        $found_if[] = $vif;

      } else if (!strcmp($f[0], 'groupname')) {
        if ($found_if[$ifname]) {
          $found_if[$ifname]['group'] = $f[1];
        }
      }
    }

    return $found_if;
  }

  public static function update_network_s11(&$s) {

    $found_if = array();
    $dladm = $s->findBin('dladm');
    $cmd_dladm = "$dladm show-phys -m";
    $out_dladm = $s->exec($cmd_dladm);
    
    $lines = explode(PHP_EOL, $out_dladm);
    
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line))
	continue;

      $f = preg_split("/\s+/", $line);
      if (count($f) != 5 || $f[0] == 'LINK') {
	continue;
      }
      $pnet = array();
      $pnet['ifname'] = $f[0];
      $pnet['layer'] = 2;
      $pnet['fk_server'] = $s->id;
      $pnet['address'] = $f[2];
      $found_if[$f[0]] = $pnet;
    }

    $ifconfig = $s->findBin('ifconfig');
    $cmd_ifconfig = "$ifconfig -a";
    $out_ifconfig = $s->exec($cmd_ifconfig);

    $lines = explode(PHP_EOL, $out_ifconfig);

    $ifname = $alias = null;
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line))
        continue;

      $f = preg_split("/\s+/", $line);

      if (preg_match('/^([a-z0-9:]*):$/', $f[0], $m)) {

        $ifname = $m[1];
	$alias = '';
	$flags = '';

        if (preg_match('/:/', $m[1])) {
          $ifname = explode(':', $m[1]);
	  $alias = $ifname[1];
          $ifname = $ifname[0];
        }
        if (preg_match('/flags=([0-9]*)<([A-Za-z0-9,]*)>/', $f[1], $m) &&
	    !preg_match('/IPv6/', $f[1])) {
          $flags = $m[2];
        }
        if (empty($alias)) {
          // physical should match $ifname already
	  if (isset($found_if[$ifname])) {
            $c_if = $found_if[$ifname];
	    if (!isset($found_if[$ifname]['flags']) ||
	        empty($found_if[$ifname]['flags'])) {
	      $found_if[$ifname]['flags'] = $flags;
	    }

	  } else {
	    $if = array();
	    $if['ifname'] = $ifname;
            $if['layer'] = 2;
            $if['flags'] = $flags;
            $if['fk_server'] = $s->id;
	    $found_if[$ifname] = $if;
	  }
        }

      } else if (!strcmp($f[0], 'inet') && strcmp($f[1], '0.0.0.0') && $f[1] != 0) {

        $vif = array();
	$vif['ifname'] = $ifname;
	$vif['alias'] = $alias;
	$vif['layer'] = 3;
	$vif['version'] = 4;
	$vif['address'] = $f[1];
        $vif['fk_server'] = $s->id;
        if (!strcmp($f[2], 'netmask')) {
	  $vif['netmask'] = $f[3];
	}
        $found_if[] = $vif;

      } else if (!strcmp($f[0], 'inet6') && strcmp($f[1], '::/0')) {

        $vif = array();
	$vif['ifname'] = $ifname;
	$vif['alias'] = $alias;
	$vif['layer'] = 3;
        $vif['fk_server'] = $s->id;
	$vif['version'] = 6;
	$vif['address'] = $f[1];
        if (preg_match('/\//', $vif['address'])) {
          $vif['address'] = explode('/', $vif['address']);
	  $vif['netmask'] = $vif['address'][1];
	  $vif['address'] = $vif['address'][0];
        }
        if (count($f) > 3 && !strcmp($f[2], 'netmask')) {
	  $vif['netmask'] = $f[3];
	}
        $found_if[] = $vif;

      } else if (!strcmp($f[0], 'groupname')) {
        if (isset($found_if[$ifname])) {
          $found_if[$ifname]['group'] = $f[1];
          $found_if[$ifname]['f_ipmp'] = 1;
        }
      }
    }

    return $found_if;
  }

  public static function update_network(&$s) {

    if ($s->data('os:major') > 10) {
      $ifs = OSSolaris::update_network_s11($s);
    } else {
      $ifs = OSSolaris::update_network_s10($s);
    }

    $f = array(
		'ifname',
		'fk_server',
		'alias',
		'layer',
		'version',
		'address',
	 );
    $fa = array(
		'netmask',
		'group',
		'flags',
		'f_ipmp',
	);
    foreach($ifs as $if) {
      $io = new Net();
      $upd = false;
      foreach($f as $fi) {
        if (isset($if[$fi])) {
  	  $io->{$fi} = $if[$fi];
	}
      }
      if ($io->fetchFromFields($f)) {
	$io->insert();
	$s->log("Added $io", LLOG_INFO);
      }
      foreach($fa as $fi) {
        if (isset($if[$fi])) {
	  if (strcmp($io->{$fi}, $if[$fi])) {
            $io->{$fi} = $if[$fi];
 	    $upd = true;
	    $s->log("Changed $io $fi to be ".$if[$fi], LLOG_DEBUG);
	  }
        }
      } 
      $found_if[''.$io] = $io;
      if ($upd) {
	$io->update();
      }
    }

    foreach($s->a_net as $n) {
      if (isset($found_if[''.$n])) {
        continue;
      }
      $s->log("Removing net $n", LLOG_INFO);
      $n->delete();
    }

    /* default router */

    $netstat = $s->findBin('netstat');
    $cmd_netstat = "$netstat -rn";
    $out_netstat = $s->exec($cmd_netstat);

    $lines = explode(PHP_EOL, $out_netstat);
    $defrouter = null;

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line))
        continue;

      $f = preg_split("/\s+/", $line);

      if (!strcmp($f[0], 'default')) {
        $defrouter = $f[1];
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
   * sneep
   */
  public static function update_sneep(&$s) {

    /* get sneep */
    $paths = OSSolaris::$binPaths;
    $paths[] = '/opt/SUNWsneep/bin';
    $sneep = $s->findBin('sneep', $paths);

    $cmd_sneep = "$sneep";
    $out_sneep = $s->exec($cmd_sneep);

    if (!empty($out_sneep)) {
      if ($s->o_pserver) {
        if ($s->o_pserver->serial != $out_sneep) {
	  $s->o_pserver->serial = $out_sneep;
          $s->log("Updated serial number: $out_sneep", LLOG_INFO);
          $s->o_pserver->update();
        }
      }
    }
    return 0;
  }

  /**
   * cat /etc/release
   */
  public static function update_release(&$s) {

    /* get cat */
    $cat = $s->findBin('cat');

    $cmd_cat = "$cat /etc/release";
    $out_cat = $s->exec($cmd_cat);

    $release_lines = explode(PHP_EOL, $out_cat);
    $release = $release_lines[0];
    $f_release = explode(' ', $release);
    $release_major = $f_release[1];
    $release_update = $f_release[2];
    if ($release_major == 'Solaris' || $f_release[0] == 'Oracle') {
      $release_major = $f_release[2];
      $release_update = $f_release[3];
    }
    
    if ($s->data('os:major') != $release_major) {
      $s->setData('os:major', $release_major);
      $s->log('os:major => '.$release_major, LLOG_INFO);
    }

    if ($s->data('os:update') != $release_update) {
      $s->setData('os:update', $release_update);
      $s->log('os:update => '.$release_update, LLOG_INFO);
    }
    
    return 0;
  }


  /**
   * prtconf
   */
  public static function update_prtconf(&$s) {

    if ($s->data('hw:cpu') == 'sparc') {
      return 0;
    }

    /* get prtconf */
    $prtconf = $s->findBin('prtconf');

    $cmd_prtconf = "$prtconf";
    $out_prtconf = $s->exec($cmd_prtconf);

    $memsize = 0;

    $prtconf_lines = explode(PHP_EOL, $out_prtconf);
    foreach($prtconf_lines as $line) {
      $line = trim($line);
      if (preg_match('/^Memory size:/', $line)) {
        $f_mem = explode(' ', $line);
        $memsize = $f_mem[2];
        break;
      }
    }

    if ($memsize && $s->data('hw:memory') != $memsize) {
      $s->setData('hw:memory', $memsize);
      $s->log('Updating Memory size: '.$memsize, LLOG_INFO);
    }

    return 0;
  }


  /**
   * prtdiag
   */
  public static function update_prtdiag(&$s) {

    /* get prtdiag */
    $platform = $s->data('hw:platform');
    $paths = array('/usr/sbin', '/usr/platform/'.$platform.'/sbin');
    $prtdiag = $s->findBin('prtdiag', $paths);

    $cmd_prtdiag = "$prtdiag";
    $out_prtdiag = $s->exec($cmd_prtdiag);

    $str_model = '';
    $str_vendor = '';
    $memsize = 0;

    $prtdiag_lines = explode(PHP_EOL, $out_prtdiag);
    foreach($prtdiag_lines as $line) {
      $line = trim($line);
      if (preg_match('/^Memory size:/', $line)) {
        $f_mem = explode(' ', $line);
        $memsize = $f_mem[2];
        if (preg_match('/GB$/', $memsize)) {
          $memsize = preg_replace('/GB$/', '', $memsize);
	  $memsize *= 1024;
        }
        continue;
      }
      if (preg_match('/^System Configuration:/', $line)) {

        if (preg_match('/sun|oracle/i', $line)) {
          $str_vendor = 'Sun Microsystems';
	} else {
          $str_vendor = 'Unknown';
	}

        $line = preg_replace('/\(.*\)/', ' bleh', $line);
        $f_line = explode(' ', $line);
        $cnt = count($f_line);
        if ($f_line[$cnt - 1] != 'bleh' ||
	    $f_line[$cnt - 1] != 'Server' ||
	    $f_line[$cnt - 1] != 'SERVER') {
	  $str_model = $f_line[$cnt - 1];
 	} else {
	  $str_model = $f_line[$cnt - 2];
	}
        continue;
      }
    }

    if ($s->data('hw:cpu') == 'sparc' && $s->data('hw:memory') != $memsize && $memsize) {
      $s->setData('hw:memory', $memsize);
      $s->log('Updating Memory size: '.$memsize, LLOG_INFO);
    }

    $mo = new Model();
    $mo->name = $str_model;
    $mo->vendor = $str_vendor;
    if ($mo->fetchFromFields(array('name', 'vendor'))) {
      $mo->insert();
    }
    if ($s->o_pserver) {
      if ($mo->id != $s->o_pserver->fk_model) {
        $s->log('Updating HW Model to be: '.$mo, LLOG_INFO);
        $s->o_pserver->fk_model = $mo->id;
        $s->o_pserver->update();
      }
    }

    return 0;
  }


  /**
   * uname -a
   */
  public static function update_uname(&$s) {

    /* get uname -a */
    $uname = $s->findBin('uname');
    $cmd_uname = "$uname -a";
    $out_uname = $s->exec($cmd_uname);

    $f_uname = explode(' ', $out_uname);
    
    $os_version = $f_uname[2];
    $os_version = preg_replace('/5\/./', '', $os_version);
    $os_version = $os_version[count($os_version) - 1];

    $kr_version = $f_uname[3];
    $kr_version = preg_replace('/Generic_/', '', $kr_version);
    $hw_class = $f_uname[4];
    $cputype = $f_uname[5];
    $platform = $f_uname[count($f_uname) - 1]; 

    $s->setData('os:version', $os_version);
    $s->setData('os:kernel', $kr_version);
    $s->setData('hw:class', $hw_class);
    $s->setData('hw:cpu', $cputype);
    $s->setData('hw:platform', $platform);

    return 0;
  }

  /**
   * uname -a
   */
  public static function update_projects(&$s) {

    $cat = $s->findBin('cat');
    $cmd_cat = "$cat /etc/project";
    $out_cat = $s->exec($cmd_cat);

    $found_p = array();
    
    $lines = explode(PHP_EOL, $out_cat);

    foreach($lines as $line) {
      $line = trim($line);
      if(empty($line) || preg_match('/^#/', $line)) {
        continue;
      }
      $f = explode(':', $line);
      $po = new Prj();
      $po->fk_server = $s->id;
      $po->prjid = $f[1];
      if ($po->fetchFromFields(array('fk_server', 'prjid'))) {
        $po->insert();
      }
      $changed = false;
      if (strcmp($po->name, $f[0])) {
	$changed = true;
	$po->name = $f[0];
	$s->log("Changed prj ".$po->prjid." name => ".$po->name, LLOG_DEBUG);
      }
      if (strcmp($po->comment, $f[2])) {
	$changed = true;
	$po->comment = $f[2];
	$s->log("Changed prj ".$po->prjid." comment => ".$po->comment, LLOG_DEBUG);
      }
      if (strcmp($po->ulist, $f[3])) {
	$changed = true;
	$po->ulist = $f[3];
	$s->log("Changed prj ".$po->prjid." ulist => ".$po->ulist, LLOG_DEBUG);
      }
      if (strcmp($po->glist, $f[4])) {
	$changed = true;
	$po->glist = $f[4];
	$s->log("Changed prj ".$po->prjid." glist => ".$po->glist, LLOG_DEBUG);
      }
      if (strcmp($po->attrs, $f[5])) {
	$changed = true;
	$po->attrs = $f[5];
	$s->log("Changed prj ".$po->prjid." attrs => ".$po->attrs, LLOG_DEBUG);
      }
      if($changed) $po->update();

      $found_p[$po->prjid] = $po;
      $s->a_prj[] = $po;
    }

    foreach($s->a_prj as $p) {
      if (isset($found_p[$p->prjid])) {
        continue;
      }
      $s->log("Removing prj $p", LLOG_INFO);
      $p->delete();
    }

  }

  /**
   * fcinfo
   */
  public static function update_fcinfo(&$s) {

    /* Port infos */
    $fcinfo = $s->findBin('fcinfo');
    $sudo = $s->findBin('sudo');
    $cmd_fcinfo = "$sudo $fcinfo hba-port";
    $out_fcinfo = $s->exec($cmd_fcinfo);

    $lines = explode(PHP_EOL, $out_fcinfo);
    $found_hba = array();
    $cur_hba = null;
    $cur_lun = null;
    $changed = false;
    $run_vendors = array();

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
	continue;
      }
      $f = explode(':', $line, 2);
      if (count($f) != 2) {
        continue;
      }
      $f[0] = trim($f[0]);
      $f[1] = trim($f[1]);

      switch($f[0]) {
        case 'HBA Port WWN':
	  if ($cur_hba) {
	    if ($changed) $cur_hba->update();
	    $cur_hba = null;
	  }
	  $changed = false;
	  $cur_hba = new Hba();
	  $cur_hba->fk_server = $s->id;
	  $cur_hba->wwn = $f[1];
	  if ($cur_hba->fetchFromFields(array('fk_server', 'wwn'))) {
            $s->log("Added HBA $cur_hba", LLOG_INFO);
	    $cur_hba->insert();
	    $s->a_hba[] = $cur_hba;
	  }
	  $found_hba[$cur_hba->wwn] = $cur_hba;
	break;
	case 'OS Device Name':
	  $dev = preg_replace('/\/dev\/cfg\//', '', $f[1]);
	  if ($cur_hba && strcmp($cur_hba->osdev, $dev)) {
	    $cur_hba->osdev = $dev;
	    $changed = true;
	    $s->log("changed $cur_hba osdev => $dev", LLOG_DEBUG);
	  }
	break;
	case 'Manufacturer':
	  $vendor = $f[1];
	  if ($cur_hba && strcmp($cur_hba->vendor, $vendor)) {
	    $cur_hba->vendor = $vendor;
	    $changed = true;
	    $s->log("changed $cur_hba vendor => $vendor", LLOG_DEBUG);
	  }
	break;
	case 'Model':
	  $model = $f[1];
	  if ($cur_hba && strcmp($cur_hba->model, $model)) {
	    $cur_hba->model = $model;
	    $changed = true;
	    $s->log("changed $cur_hba model => $model", LLOG_DEBUG);
	  }
	break;
	case 'Firmware Version':
	  $firmware = $f[1];
	  if ($cur_hba && strcmp($cur_hba->firmware, $firmware)) {
	    $cur_hba->firmware = $firmware;
	    $changed = true;
	    $s->log("changed $cur_hba firmware => $firmware", LLOG_DEBUG);
	  }
	break;
	case 'FCode/BIOS Version':
	  $fcode = $f[1];
	  if ($cur_hba && strcmp($cur_hba->fcode, $fcode)) {
	    $cur_hba->fcode = $fcode;
	    $changed = true;
	    $s->log("changed $cur_hba fcode => $fcode", LLOG_DEBUG);
	  }
	break;
	case 'Serial Number':
	  $serial = $f[1];
	  if ($cur_hba && strcmp($cur_hba->serial, $serial)) {
	    $cur_hba->serial = $serial;
	    $changed = true;
	    $s->log("changed $cur_hba serial => $serial", LLOG_DEBUG);
	  }
	break;
	case 'Driver Name':
	  $drv = $f[1];
	  if ($cur_hba && strcmp($cur_hba->drv, $drv)) {
	    $cur_hba->drv = $drv;
	    $changed = true;
	    $s->log("changed $cur_hba drv => $drv", LLOG_DEBUG);
	  }
	break;
	case 'Driver Version':
	  $drv_ver = $f[1];
	  if ($cur_hba && strcmp($cur_hba->drv_ver, $drv_ver)) {
	    $cur_hba->drv_ver = $drv_ver;
	    $changed = true;
	    $s->log("changed $cur_hba drv_ver => $drv_ver", LLOG_DEBUG);
	  }
	break;
	case 'State':
	  $state = $f[1];
	  if ($cur_hba && strcmp($cur_hba->state, $state)) {
	    $cur_hba->state = $state;
	    $changed = true;
	    $s->log("changed $cur_hba state => $state", LLOG_DEBUG);
	  }
	break;
	case 'Supported Speeds':
	break;
	case 'Current Speed':
	  $curspeed = $f[1];
	  if ($cur_hba && strcmp($cur_hba->curspeed, $curspeed)) {
	    $cur_hba->curspeed = $curspeed;
	    $changed = true;
	    $s->log("changed $cur_hba curspeed => $curspeed", LLOG_DEBUG);
	  }
	break;
	case 'Node WWN':
	break;
      }
    }
    if ($changed && $cur_hba) $cur_hba->update();

    foreach($s->a_hba as $p) {
      if (isset($found_hba[$p->wwn])) {
        continue;
      }
      $s->log("Removing hba $p", LLOG_INFO);
      $p->delete();
    }

    /* update luns */
    $found_lun = array();

    foreach($s->a_hba as $hba) {
      $s->log("Updating hba $hba", LLOG_INFO);
      $cmd_fcinfo = "$sudo $fcinfo remote-port -sl -p ".$hba->wwn;
      $out_fcinfo = $s->exec($cmd_fcinfo);

      $lines = explode(PHP_EOL, $out_fcinfo);
      $cur_lun = null;
      
      foreach($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
          continue;
        }
        $f = explode(':', $line, 2);
	if (count($f) != 2) {
	  continue;
	}
	switch($f[0]) {
	  case 'LUN':
            if($cur_lun) {
	      $found_lun[] = $cur_lun;
	      $cur_lun = array();
	    }
	  break;
	  case 'Vendor':
	    $cur_lun['vendor'] = trim($f[1]);
	  break;
	  case 'Product':
	    $cur_lun['product'] = trim($f[1]);
	  break;
	  case 'OS Device Name':
	    $dev = trim($f[1]);
	    $dev = preg_replace('/^\/dev\/rdsk\//', '', $dev);
	    $dev = preg_replace('/s2$/', '', $dev);
	    $cur_lun['dev'] = $dev;
	  break;
	}
      }
      if ($cur_lun) {
        $found_lun[] = $cur_lun;
      }
    }
    $s->log("Found ".count($found_lun)." luns", LLOG_INFO);
    foreach($found_lun as $lun) {
      
      $upd = false;
      $do = new Disk();
      $do->fk_server = $s->id;
      $do->dev = $lun['dev'];
      if (!strcmp($do->dev, 'Unknown')) {
	continue;
      }
      if ($do->fetchFromFields(array('dev', 'fk_server'))) {
        $s->log("Added $do", LLOG_INFO);
        $do->insert();
      }
      if (!$do->f_san || $do->f_local) {
        $do->f_san = 1;
        $do->f_local = 0;
        $upd = true;
        $s->log("set $do f_local => 0", LLOG_DEBUG);
        $s->log("set $do f_san => 1", LLOG_DEBUG);
      }
      if (isset($lun['vendor']) && !empty($lun['vendor']) &&
	  strcmp($lun['vendor'], $do->vendor)) {
	$do->vendor = $lun['vendor'];
        $s->log("set $do vendor => ".$do->vendor, LLOG_DEBUG);
        $upd = true;
      }
      if (isset($lun['product']) && !empty($lun['product']) &&
          strcmp($lun['product'], $do->product)) {
        $do->product = $lun['product'];
        $s->log("set $do product => ".$do->product, LLOG_DEBUG);
        $upd = true;
      }

      $mpxio = false;

      /* MPxIO */
      if (preg_match('/^c[0-9]*t6/', $do->dev) && strcmp($do->drv, 'MPxIO')) {
        $do->drv = 'MPxIO';
        $s->log("set $do drv => ".$do->drv, LLOG_DEBUG);
        $upd = true;
	$mpxio = true;
      } else if (!strcmp($do->drv, 'MPxIO')) {
	$mpxio = true;
      }

      if (isset($lun['vendor']) && !empty($lun['vendor'])) {
        switch($lun['vendor']) {
	  case 'HP':
	    if (!strncmp($lun['product'], 'OPEN-V', 6)) {
	      /* With HP OPENV, we can guess the lunid
	       * using the MPxIO device name if we
	       * are using MPxIO
               */
	      if ($mpxio) {
	        $lunid = substr($do->dev, 32, 4);
		if ($lunid && !empty($lunid) && strcmp($do->lunid, $lunid)) {
                  $do->lunid = $lunid;
                  $s->log("set $do lunid => ".$do->lunid, LLOG_DEBUG);
                  $upd = true;
		}
	      } else {
		$run_vendors['HP'] = true; /* set this to run xpinfo afterwards */
	      }
	    }
	  break;
	  case 'EMC':
	   if ($mpxio) {
 	     $run_vendors['EMC_MPXIO'] = true;
	   } else {
 	     $run_vendors['EMC'] = true;
	   }
	  break;
        }
      }

      if ($upd) $do->update();
    }

    foreach($run_vendors as $v => $k) {
      if (!$k)
	continue;

      $s->log("Found $v, trying to run specific routine...", LLOG_INFO);
 
      try {
        switch($v) {
	  case 'HP':
	    OSSolaris::update_disk_hp($s);
	  break;
	  case 'EMC':
	    OSSolaris::update_disk_emc($s);
	  break;
  	  case 'EMC_MPXIO':
  	    OSSolaris::update_disk_emc_mpxio($s);
    	  break;
        }
      } catch (Exception $e) {
        $s->log('Exception caught: '.$e, LLOG_ERR);
      }
    }

    return 0;
  }

  public static function update_disk_hp(&$s) {

    $sudo = $s->findBin('sudo');
    $xpinfo = $s->findBin('xpinfo');
    $cmd_xpinfo = "$sudo $xpinfo -d";
    $out_xpinfo = $s->exec($cmd_xpinfo);

    echo "$out_xpinfo\n";

    return 0;
  }


  public static function update_disk_emc_mpxio(&$s) {

    $paths = OSSolaris::$binPaths;
    $paths[] = '/opt/emc/SYMCLI/bin';
    $paths[] = '/usr/symcli/bin';

    $sudo = $s->findBin('sudo');
    $syminq = $s->findBin('syminq', $paths);
    $cmd_syminq = "$sudo $syminq -symmids";
    $out_syminq = $s->exec($cmd_syminq);
    
    $lines = explode(PHP_EOL, $out_syminq);
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) 
	continue;

      if (preg_match('/^\/dev\/rdsk\/(c[0-9]*t[0-9A-Z]*d0)s2/', $line, $m)) {
        $do = new Disk();
	$do->fk_server = $s->id;
	$do->dev = $m[1];
        if ($do->fetchFromFields(array('fk_server', 'dev'))) {
	  $s->log("Disk $do not found", LLOG_WARN);
	  continue;
        }
        $upd = false;
        $f = preg_split("/\s+/", $line);
        $c = count($f);
        $cap = $f[$c - 1];
        $serial = $f[$c - 2];
	if (!empty($serial) && strcmp($serial, $do->serial)) {
	  $do->serial = $serial;
	  $s->log("changed $do serial => $serial", LLOG_DEBUG);
	  $upd = true;
	}
	$lid = substr($serial, 3, 4);
	/**
	 * With EMC, lunid is not relevant to the disk but to the host lun,
	 * se we'll fill the lunid field with the device id which is part of the disk serial
	 */
	if ($lid && strcmp($lid, $do->lunid)) {
	  $do->lunid = $lid;
	  $upd = true;
	  $s->log("changed $do did => $lid", LLOG_DEBUG);
	}
        if ($cap && $do->size <= 0) {
          $do->size = $cap * 1024;
          $s->log("changed $do size => ".$do->size, LLOG_DEBUG);
          $upd = true;
        }
	if ($upd) $do->update();
      }
    }

    return 0;
  }


  public static function update_disk_emc(&$s) {

    $paths = OSSolaris::$binPaths;
    $paths[] = '/etc';

    $sudo = $s->findBin('sudo');
    $powermt = $s->findBin('powermt', $paths);

    $cmd_powermt = "$sudo $powermt display dev=all";
    $out_powermt = $s->exec($cmd_powermt);

    echo "$out_powermt\n";

    return 0;
  }

  /**
   * disk
   */
  public static function update_disk(&$s) {
    
    $ls = $s->findBin('ls');
    $cmd_ls = "$ls /dev/dsk/*s2";
    $out_ls = $s->exec($cmd_ls);

    $lines = explode(PHP_EOL, $out_ls);
    $found_d = array();

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }

      $dname = preg_replace('/s2$/', '', $line);
      $dname = preg_replace('/^\/dev\/dsk\//', '', $dname);
      $do = new Disk();
      $do->fk_server = $s->id;
      $do->dev = $dname;
      if ($do->fetchFromFields(array('dev', 'fk_server'))) {
        $s->log("Added $do", LLOG_INFO);
        $do->insert();
      }
      $found_d[$dname] = $do;
      $s->a_disk[] = $do;
    }

    $iostat = $s->findBin('iostat');
    $cmd_iostat = "$iostat -En";
    $out_iostat = $s->exec($cmd_iostat);
    
    $lines = explode(PHP_EOL, $out_iostat);
    $cur_disk = null;
    $fobj = array('size', 'vendor', 'product', 'rev', 'serial');

    $vars = array();
    $imdone = false;
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }
      if (preg_match('/^(c[0-9]*t[A-Z0-9]*d[0-9]*)/', $line, $m)) {
        if ($cur_disk) {
	  $changed = false;
          foreach($fobj as $f) {
	    if (isset($vars[$f]) && !empty($vars[$f])
	        && strcmp($vars[$f], $cur_disk->{$f})) {
	      $changed = true;
	      $cur_disk->{$f} = $vars[$f];
	      $s->log("changed $cur_disk $f => ".$cur_disk->{$f}, LLOG_DEBUG);
	    }
	  }
          if ($changed) {
	    $cur_disk->update();
	  }
	  $cur_disk = null;
	}
        if (isset($found_d[$m[1]])) {
	  $cur_disk = $found_d[$m[1]];
        } else {
	  $d = new Disk();
	  $d->dev = $m[1];
	  $d->fk_server = $s->id;
	  if ($d->fetchFromFields(array('fk_server', 'dev'))) {
	    $d->insert();
            $s->log("Added $d", LLOG_INFO);
	  }
	  $found_d[$d->dev] = $d;
	  $s->a_disk[] = $d;
	  $cur_disk = $d;
	}
	$vars = array();
	$imdone = false;
        continue;
      } else if (!$imdone && preg_match('/^Vendor: (.*) Product: (.*)Revision: (.*)Serial No:(.*)$/', $line, $m)) {
        $vars['vendor'] = trim($m[1]);
        $vars['product'] = trim($m[2]);
        $vars['rev'] = trim($m[3]);
        $vars['serial'] = trim($m[4]);
      } else if (!$imdone && preg_match('/^Size: (.*)$/', $line, $m)) {
        $size = trim($m[1]);
	if (preg_match('/^[0-9\.]*GB <([0-9]*) bytes>/', $size, $m)) {
	  $size = $m[1];
	}
	$vars['size'] = $size;
        $imdone = true;
      }
    }
    if ($cur_disk) {
      $changed = false;
      foreach($fobj as $f) {
        if (isset($vars[$f])  && !empty($vars[$f])
	    && strcmp($vars[$f], $cur_disk->{$f})) {
          $changed = true;
          $cur_disk->{$f} = $vars[$f];
          $s->log("changed $cur_disk $f => ".$cur_disk->{$f}, LLOG_DEBUG);
        }
      }
      if ($changed) {
        $cur_disk->update();
      }
    }

    foreach($s->a_disk as $p) {
      if (isset($found_d[$p->dev])) {
        continue;
      }
      $s->log("Removing disk $p", LLOG_INFO);
      $p->delete();
    }

    return 0;
  }

  /**
   * CDP
   */
  public static function update_cdp(&$s) {
 

    $sudo = $s->findBin('sudo');
    $snoop = $s->findBin('snoop');
    $cmd_snoop = "$sudo $snoop -P -x 0 -c 1 -r -s 1600 -d %s ether dst 01:00:0c:cc:cc:cc and greater 150";

    $s->fetchRL('a_net');

    foreach($s->a_net as $net) {
      if ($net->layer != 2) {
	continue;
      }
      if (!strncmp($net->ifname, 'lo', 2) || !strncmp($net->ifname, 'ipmp', 4)) {
	continue;
      }
      if (!preg_match('/UP/i', $net->flags) || !preg_match('/RUNNING/i', $net->flags) ||
	 preg_match('/FAILED/i', $net->flags)) {
	continue;
      }
      $s->log("checking for CDP packet on $net", LLOG_INFO);
      try {
        $out_snoop = $s->exec($cmd_snoop, array($net->ifname), 100);
      } catch (Exception $e) {
	$s->log("Error checking CDP for $net: $e", LLOG_WARN);
	continue;
      }
      if (!empty($out_snoop)) {
        $cdpp = new CDPPacket('snoop', $out_snoop);
        $cdpp->treat();
        $ns = null;
	/* check switch */
	if (isset($cdpp->ent['deviceid']) && !empty($cdpp->ent['deviceid'])) {
	  $ns = new NSwitch();
	  $ns->did = $cdpp->ent['deviceid'];
	  $upd = false;
	  if ($ns->fetchFromField('did')) {
	    $s->log("Added new switch $ns", LLOG_INFO);
	    $ns->insert();
	  }
	  if (isset($cdpp->ent['sfversion']) &&
	      !empty($cdpp->ent['sfversion']) &&
	      strcmp($cdpp->ent['sfversion'], $ns->sfver)) {
	    $upd = true;
	    $ns->sfver = $cdpp->ent['sfversion'];
	    $s->log("updated sfver of $ns", LLOG_DEBUG);
	  }
          if (isset($cdpp->ent['platform']) && 
              !empty($cdpp->ent['platform']) &&
              strcmp($cdpp->ent['platform'], $ns->platform)) {
            $upd = true;
            $ns->platform = $cdpp->ent['platform'];
            $s->log("updated platform of $ns -> ".$ns->platform, LLOG_DEBUG);
          }
          if (isset($cdpp->ent['name']) && 
              !empty($cdpp->ent['name']) &&
              strcmp($cdpp->ent['name'], $ns->name)) {
            $upd = true;
            $ns->name = $cdpp->ent['name'];
            $s->log("updated name of $ns -> ".$ns->name, LLOG_DEBUG);
          }
          if (isset($cdpp->ent['location']) &&  
              !empty($cdpp->ent['location']) &&
              strcmp($cdpp->ent['location'], $ns->location)) {
            $upd = true;
            $ns->location = $cdpp->ent['location'];
            $s->log("updated location of $ns -> ".$ns->location, LLOG_DEBUG);
          }
	  if ($upd) $ns->update();
	}
	/* Check interface */
	if (isset($cdpp->ent['port']) && !empty($cdpp->ent['port'])) {
	  if (!$ns) continue; // no switch...
          $ns->fetchRL('a_net');
	  $sif = new Net();
	  $sif->fk_switch = $ns->id;
	  $sif->ifname = $cdpp->ent['port'];
	  $upd = false;
	  if ($sif->fetchFromFields(array('fk_switch', 'ifname'))) {
	    $sif->insert();
	    $s->log("added $sif to $ns", LLOG_INFO);
	  }
          if ($sif->fk_net <= 0 || $sif->fk_net != $net->id) {
	    $s->log("changed link for $ns/$sif => $net", LLOG_DEBUG);
	    $sif->fk_net = $net->id;
	    $net->fk_net = $sif->id;
	    $upd = true;
	  }
	  if ($net->fk_net <= 0 || $net->fk_net != $sif->id) {
	    $s->log("changed link for $net => $ns/$sif", LLOG_DEBUG);
	    $sif->fk_net = $net->id;
	    $net->fk_net = $sif->id;
	    $upd = true;
	  }
	  /**
	   * @TODO: Add details to switch interfaces like mtu, duplex, link,vlan, etc..
	   */
	  if ($upd) {
	    $sif->update();
	    $net->update();
	  }
	}
      }
    }

  }

  /**
   * sds
   */
  public static function update_sds(&$s) {
 
    $metastat = $s->findBin('metastat');
    $metaset = $s->findBin('metaset');

  }


  /**
   * zfs
   */
  public static function update_zfs(&$s) {

    if ($s->data('os:major') < 10) {
      return 0;
    }

    $zpool = $s->findBin('zpool');

    $cmd_zpool = "$zpool list -H -o name,size,free,capacity";
    $cmd_ozpool = "$zpool list -H -o name,size,available,capacity";

    $out_zpool = $s->exec($cmd_zpool);

    if (!strcmp(trim($out_zpool), "no pools available")) { /* no pools */
      return 0;
    }

    if (empty($out_zpool)) {
      $out_zpool = $s->exec($cmd_ozpool);
      if (!strcmp(trim($out_zpool), "no pools available")) { /* no pools */
        return 0;
      }
    }

    $lines = explode(PHP_EOL, $out_zpool);
    $found_z = array();
    $upd = false;

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }
      $f = preg_split("/\s+/", $line);
      $name = $f[0];
      $size = Pool::formatSize($f[1]);
      $free = Pool::formatSize($f[2]);
      $used = $size - $free;
      $p = new Pool();
      $p->fk_server = $s->id;
      $p->name = $name;
      $upd = false;
      if ($p->fetchFromFields(array('fk_server', 'name'))) {
	$s->log("Adding pool $p", LLOG_INFO);
	$p->insert();
        $s->a_pool[] = $p;
      }
      if ($size != $p->size) {
        $p->size = $size;
	$upd = true;
	$s->log("Changed pool $p size => $size", LLOG_INFO);
      }
      if ($used != $p->used) {
        $p->used = $used;
	$upd = true;
	$s->log("Changed pool $p used => $used", LLOG_INFO);
      }
      if ($upd) $p->update();
      $found_z[$p->name] = $p;
    }
    foreach($s->a_pool as $p) {
      if (isset($found_z[$p->name])) {
        continue;
      }
      $s->log("Removing pool $p", LLOG_INFO);
      $p->delete();
    }

    /* Update zpool devices */

    $cmd_status = "$zpool status %s";
    $zfs = $s->findBin('zfs');
    $cmd_dset = "$zfs list -H -r -t filesystem,volume -o name,used,quota,available %s";

    foreach($s->a_pool as $p) {
      $p->fetchJT('a_disk');
      $p->fetchRL('a_dataset');
      $cmd_s = sprintf($cmd_status, $p->name);
      $cmd_d = sprintf($cmd_dset, $p->name);
      $out_s = $s->exec($cmd_s);

      $lines = explode(PHP_EOL, $out_s);

      $vdev_list = false;
      $found_v = array();

      foreach($lines as $line) {
	$line = trim($line);
	if (empty($line))
	  continue;

        if (!$vdev_list && preg_match('/^NAME/', $line)) {
	  $vdev_list = true;
	  continue;
	}

	if ($vdev_list && preg_match('/^errors:/', $line)) {
	  $vdev_list = false;
	  continue;
	}

        if ($vdev_list && !preg_match('/^mirror|^raid|^log|^spare|^cache/', $line)) {

	  /* we should have a dev here... */
          $f = preg_split("/\s+/", $line);
	  $dev = $f[0];
	  if (!strcmp($dev, $p->name))
	    continue;

	  $dev = preg_replace('/^\/dev\/rdsk\//', '', $dev);
	  $dev = preg_replace('/^\/dev\/dsk\//', '', $dev);
	  $slice = 2;
	  if (preg_match('/s([0-9])$/', $dev, $m)) {
	    $slice = $m[1];
	    $dev = preg_replace('/s[0-9]$/', '', $dev);
          }
   	  $do = new Disk();
	  $do->fk_server = $s->id;
	  $do->dev = $dev;
	  $do->slice[''.$p] = $slice;
	  if ($do->fetchFromFields(array('fk_server', 'dev'))) {
	    $s->log("Disk $do was not found on $s for pool $p", LLOG_ERR);
	    continue;
	  }
	  
 	  if (!$p->isInJT('a_disk', $do, array('slice'))) {
	    $s->log("add $do slice $slice to $p", LLOG_INFO);
	    $p->addToJT('a_disk', $do);
	  }
          $found_v[$do->dev] = $do;
	  
	  continue;
        }
      }
      foreach($p->a_disk as $d) {
        if (isset($found_v[$d->dev])) {
          continue;
        }
        $s->log("Removing disk $d from pool $p", LLOG_INFO);
        $p->delFromJT('a_disk', $d);
      }

      /* dataset ndexation */
      $found_d = array();
      $out_d = $s->exec($cmd_d);
      $lines = explode(PHP_EOL, $out_d);

      foreach($lines as $line) {
	$line = trim($line);
	if (empty($line))
	  continue;
        
        $f = preg_split("/\s+/", $line);
        $name = $f[0];
	$name = preg_replace("/^".$p->name."\//", '', $name);
	$do = new Dataset();
	$do->name = $name;
	$do->fk_pool = $p->id;
	$upd = false;
	if ($do->fetchFromFields(array('fk_pool', 'name'))) {
	  $s->log("Added dataset $do to $p", LLOG_INFO);
	  $do->insert();
	}
	$used = Pool::formatSize($f['1']);
	$quota = Pool::formatSize($f['2']);
	$available = Pool::formatSize($f['3']);
	if ($quota && $do->size != $quota) {
	  $upd = true;
	  $s->log("updated $do size => $quota", LLOG_DEBUG);
	  $do->size = $quota;
	}
	if ($used && $do->used != $used) {
	  $upd = true;
	  $s->log("updated $do used => $used", LLOG_DEBUG);
	  $do->used = $used;
	}
	if ($upd) $do->update();
	$found_d[$do->name]  = $d;
      }
      foreach($p->a_dataset as $d) {
        if (isset($found_d[$d->name])) {
          continue;
        }
        $s->log("Removing dataset $d from pool $p", LLOG_INFO);
        $d->delete();
      }
    }
  }

  /* Screening */
  public static function dump($s) {

    $ker_ver = $s->data('os:kernel');
    $sol_ver = $s->data('os:major');
    $sol_upd = $s->data('os:update');
    if (empty($ker_ver)) $ker_ver = null;
    if (empty($sol_ver)) $sol_ver = null;
    if (empty($sol_upd)) $sol_upd = null;
    $txt = '';
    $txt .= $s->o_os->name.' ';
    $txt .= ($sol_ver)?($sol_ver.' '):'';
    $txt .= ($sol_upd)?('Update '.$sol_upd.' '):'';
    $txt .= ($ker_ver)?('/ Kernel: '.$ker_ver.' '):'';

    $s->log(sprintf("%15s: %s", 'OS', $txt), LLOG_INFO);
    $s->log(sprintf("%15s: %s", 'Projects', count($s->a_prj).' found'), LLOG_INFO);
  }


}

?>
