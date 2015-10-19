<?php

class OSAix extends OSType
{
  public static $binPaths = array(
    "/usr/bin",
    "/usr/sbin",
    "/etc",
    "/bin",
    "/usr/local/bin",
    "/usr/local/sbin",
  );

  protected static $_update = array(
      'Server' => array(
        "updatePrtConf",
        "updateLParStat",
        "updateHostId",
        "updateOsLevel",
        "updateUname",
        "updateNetwork",
      //  "updateNfsShares",
      //  "updateNfsMounts",
      ),
  );

  /* updates function for AIX */

  public static function updateNetworkIfs(&$s)
  {
      $found_if = array();

      $sudo = $s->findBin('sudo');
      $ifconfig = $s->findBin('ifconfig');
      $cmd_ifconfig = "$sudo $ifconfig -a";
      $out_ifconfig = $s->exec($cmd_ifconfig);

      $lines = explode(PHP_EOL, $out_ifconfig);

      $ifname = $c_if = $c_vif = $alias = null;
      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }

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
            /* Address hereunder... */
            $if['addr'] = array();
              $if['caddr'] = 0;
              $found_if[$ifname] = $if;
          }
                  $c_if = &$found_if[$ifname];
              }
          } elseif (!strcmp($f[0], 'ether')) {
              if (isset($found_if[$ifname])) {
                  $found_if[$ifname]['address'] = $f[1];
              }
          } elseif (!strcmp($f[0], 'inet') && strcmp($f[1], '0.0.0.0') && $f[1] != 0) {
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
              if (isset($found_if[$ifname])) {
                  $c_vif = $found_if[$ifname]['caddr'];
                  $found_if[$ifname]['addr'][$c_vif] = $vif;
                  $found_if[$ifname]['caddr']++;
              }
          } elseif (!strcmp($f[0], 'inet6') && strcmp($f[1], '::/0')) {
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
              if (isset($found_if[$ifname])) {
                  $c_vif = $found_if[$ifname]['caddr'];
                  $found_if[$ifname]['addr'][$c_vif] = $vif;
                  $found_if[$ifname]['caddr']++;
              }
          } elseif (!strcmp($f[0], 'groupname')) {
              if ($found_if[$ifname]) {
                  $found_if[$ifname]['group'] = $f[1];
                  $found_if[$ifname]['f_ipmp'] = 1;
              }
          }
      }

      return $found_if;
  }

    public static function updateNetwork(&$s)
    {
        $ifs = OSAix::updateNetworkIfs($s);

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

        $bifs = array();
        foreach ($ifs as $if) {
            $bifs[] = $if;
            $bifs = array_merge($bifs, $if['addr']);
        }
        $found_if = array();
        foreach ($bifs as $if) {
            $io = new Net();
            $upd = false;
            foreach ($f as $fi) {
                if (isset($if[$fi])) {
                    $io->{$fi} = $if[$fi];
                }
            }
            if ($io->fetchFromFields($f)) {
                $io->insert();
                $s->log("Added $io", LLOG_INFO);
            }
            foreach ($fa as $fi) {
                if (isset($if[$fi])) {
                    if (strcmp($io->{$fi}, $if[$fi])) {
                        $io->{$fi} = $if[$fi];
                        $upd = true;
                        $s->log("Changed $io $fi to be ".$if[$fi], LLOG_DEBUG);
                    }
                }
            }
            $io->fetchAll();
            $found_if[''.$io] = $io;
            if ($upd) {
                $io->update();
            }
        }

        foreach ($s->a_net as $n) {
            $n->fetchAll();
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

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

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
   * hostid
   */
  public static function updateHostId(&$s)
  {

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

    public static function updateLParStat(&$s)
    {

    /* get lparstat */
    $lparstat = $s->findBin('lparstat');

        $cmd_lparstat = "$lparstat";
        $out_lparstat = $s->exec($cmd_lparstat);

        $lines = explode(PHP_EOL, $out_lparstat);
        $nrstrand = $memsize = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^System configuration:/', $line)) {
                $line = preg_replace('/^System configuration: /', '', $line);
                $nvs = explode(' ', $line);
                foreach ($nvs as $nv) {
                    $nv = explode('=', $nv);
                    $name = $nv[0];
                    $value = $nv[1];
                    switch ($name) {
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
            $s->log('updated hw:nrstrand => '.$nrstrand, LLOG_INFO);
        }

        if ($memsize && $s->data('hw:memory') != $memsize) {
            $s->setData('hw:memory', $memsize);
            $s->log('Updating Memory size: '.$memsize, LLOG_INFO);
        }

        return 0;
    }

    public static function updatePrtConf(&$s)
    {

    /* get prtconf */
    $prtconf = $s->findBin('prtconf');

        $cmd_prtconf = "$prtconf";
        $out_prtconf = $s->exec($cmd_prtconf);

        $lines = explode(PHP_EOL, $out_prtconf);
        $nrcpu = $hwclass = $cputype = $cpuspeed = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/:/', $line)) {
                $nv = explode(':', $line, 2);
                $name = $nv[0];
                $value = trim($nv[1]);
                switch ($name) {
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
            $s->log('updated hw:nrcpu => '.$nrcpu, LLOG_INFO);
        }
        if ($s->data('hw:cpu') != $cputype) {
            $s->setData('hw:cpu', $cputype);
            $s->log('updated hw:cpu => '.$cputype, LLOG_INFO);
        }
        if ($s->data('hw:class') != $hwclass) {
            $s->setData('hw:class', $hwclass);
            $s->log('updated hw:class => '.$hwclass, LLOG_INFO);
        }
        if ($s->data('hw:cpuspeed') != $cpuspeed) {
            $s->setData('hw:cpuspeed', $cpuspeed);
            $s->log('updated hw:cpuspeed => '.$cpuspeed, LLOG_INFO);
        }

        return 0;
    }

    public static function updateOsLevel(&$s)
    {

    /* get prtconf */
    $oslevel = $s->findBin('oslevel');

        $cmd_oslevel = "$oslevel";
        $out_oslevel = $s->exec($cmd_oslevel);

        $una_fields = explode('.', $out_oslevel);
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
  public static function updateUname(&$s)
  {

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
              $s->log("updated serial number: $serial", LLOG_INFO);
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
  public static function htmlDump($s)
  {
      return array(
                'Version' => $s->data('os:major'),
                'Update' => $s->data('os:update'),
           );
  }

    public static function dump($s)
    {

    }
}
