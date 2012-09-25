<?php

class OSAix extends OSType
{
  public static $binPaths = array(
    "/usr/bin",
    "/usr/sbin",
    "/bin",
    "/usr/local/bin",
    "/usr/local/sbin",
  );

  protected static $_update = array(
    "update_prtconf",
    "update_lparstat",
    "update_hostid",
    "update_oslevel",
    "update_uname",
  //  "update_network",
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


  public static function update_lparstat(&$s) {

    /* get lparstat */
    $lparstat = $s->findBin('lparstat');

    $cmd_lparstat = "$lparstat";
    $out_lparstat = $s->exec($cmd_lparstat);

    $lines = explode(PHP_EOL, $out_lparstat);
    $nrstrand = $memsize = 0;

    foreach($lines as $line) {
      $line = trim($line);
      if (preg_match('/^System configuration:/', $line)) {
        $line = preg_replace('/^System configuration: /', '', $line);
        $nvs = explode(' ', $line);
        foreach($nvs as $nv) {
	  $nv = explode('=', $nv);
	  $name = $nv[0];
	  $value = $nv[1];
	  switch($name) {
	    case 'type':
	    break;
	    case 'mode':
	    break;
	    case 'smt':
	    break;
	    case 'lcpu':
	      $nrstrand = $value;
	    break;
	    case 'mem':
	      $memsize = $value;
	    break;
	    case 'psize':
	    break;
	    case 'ent':
	    break;
	  }
        }
      }
    }

    if ($s->data('hw:nrstrand') != $nrstrand) {
      $s->setData('hw:nrstrand', $nrstrand);
      $s->log('Updated hw:nrstrand => '.$nrstrand, LLOG_INFO);
    }

    if ($memsize && $s->data('hw:memory') != $memsize) {
      $s->setData('hw:memory', $memsize);
      $s->log('Updating Memory size: '.$memsize, LLOG_INFO);
    }

    return 0;
  }

  public static function update_prtconf(&$s) {

    /* get prtconf */
    $prtconf = $s->findBin('prtconf');

    $cmd_prtconf = "$prtconf";
    $out_prtconf = $s->exec($cmd_prtconf);

    $lines = explode(PHP_EOL, $out_prtconf);
    $nrcpu = $hwclass = $cputype = $cpuspeed = 0;

    foreach($lines as $line) {
      $line = trim($line);
      if (preg_match('/:/', $line)) {
        $nv = explode(':', $line, 2);
        $name = $nv[0];
	$value = trim($nv[1]);
	switch($name) {
	  case 'Processor Implementation Mode':
	    $hwclass = $value;
	  break;
	  case 'Processor Type':
	    $cputype = $value;
	  break;
	  case 'Number Of Processors':
	    $nrcpu = $value;
	  break;
	  case 'Processor Clock Speed':
	    $cpuspeed = $value;
	  break;
	}
      }
    }

    if ($s->data('hw:nrcpu') != $nrcpu) {
      $s->setData('hw:nrcpu', $nrcpu);
      $s->log('Updated hw:nrcpu => '.$nrcpu, LLOG_INFO);
    }
    if ($s->data('hw:cpu') != $cputype) {
      $s->setData('hw:cpu', $cputype);
      $s->log('Updated hw:cpu => '.$cputype, LLOG_INFO);
    }
    if ($s->data('hw:class') != $hwclass) {
      $s->setData('hw:class', $hwclass);
      $s->log('Updated hw:class => '.$hwclass, LLOG_INFO);
    }
    if ($s->data('hw:cpuspeed') != $cpuspeed) {
      $s->setData('hw:cpuspeed', $cpuspeed);
      $s->log('Updated hw:cpuspeed => '.$cpuspeed, LLOG_INFO);
    }

    return 0;
  }


  public static function update_oslevel(&$s) {

    /* get prtconf */
    $oslevel = $s->findBin('oslevel');

    $cmd_oslevel = "$oslevel";
    $out_oslevel = $s->exec($cmd_oslevel);

    $una_fields = explode('.', $out_oslevel);
    $os_name = 'AIX';
    $os_version = $una_fields[0];
    $os_release = $out_oslevel;

    if ($s->data('os:major') != $os_version) {
      $s->setData('os:major', $os_version);
      $s->log('os:major => '.$os_version, LLOG_INFO);
    }

    if ($s->data('os:update') != $os_release) {
      $s->setData('os:update', $os_release);
      $s->log('os:update => '.$os_release, LLOG_INFO);
    }




    return 0;
  }

  /**
   * uname
   */
  public static function update_uname(&$s) {

    /* get uname -a */
    $uname = $s->findBin('uname');

    $cmd_uname = "$uname -M";
    $out_uname = $s->exec($cmd_uname);
    $model = $out_uname;
    if (preg_match('/,/', $model)) {
      $model = explode(',', $model, 2);
       $str_vendor = $model[0];
       $str_model = $model[1];
    } else {
      $str_model = $model;
      $str_vendor = 'Unknown';
    }
    
    $cmd_uname = "$uname -m";
    $out_uname = $s->exec($cmd_uname);
    $serial = $out_uname;

    $cmd_uname = "$uname -p";
    $out_uname = $s->exec($cmd_uname);
    $platform = $out_uname;

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
      if ($s->o_pserver->serial != $serial) {
        $s->o_pserver->serial = $serial;
        $s->log("Updated serial number: $serial", LLOG_INFO);
        $s->o_pserver->update();
      }
    }

    if ($s->data('hw:platform') != $platform) {
      $s->setData('hw:platform', $platform);
      $s->log('hw:platform => '.$platform, LLOG_INFO);
    }

    return 0;
  }


  /* Screening */
  public static function htmlDump($s) {

    return array(
                'Version' => $s->data('os:major'),
                'Update' => $s->data('os:update'),
           );
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