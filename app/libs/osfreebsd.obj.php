<?php

class OSFreeBSD extends OSType
{
    public static $extraActions = array();

    public static $binPaths = array(
        "/bin",
        "/usr/bin",
        "/usr/local/bin",
        "/sbin",
        "/usr/sbin",
        "/usr/local/sbin",
    );

    protected static $_update = array(
        'Server' => array(
            "updateGroup",
            "updateUname",
            "updateDmiDecode",
            "updateSysCtl",
            "updateZfs",
            "updateNetwork",
            "updatePackages",
            "updateSwap",
        //    "updateCpu",
        //    "updateNfsShares",
        //    "updateNfsMounts",
        //    "updateDisk",
        //    "updateCdp",
        ),
  );

  public static function updateGroup(&$s) {
      return OSLinux::updateGroup($s);
  }

  /**
   * dmidecode
   */
  public static function updateDmiDecode(&$s)
  {
      $dmidecode = $s->findBin('dmidecode');
      $sudo = $s->findBin('sudo');
      $cmd_dmidecode = "$sudo $dmidecode -t 1 -q";
      $out_dmidecode = $s->exec($cmd_dmidecode);

      $lines = preg_split('/\r\n|\r|\n/', $out_dmidecode);
      $vendor = $pname = $serial = 'Unknown';

      foreach ($lines as $line) {
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

          switch ($f[0]) {
            case 'Manufacturer':
              $vendor = $f[1];
            break;
            case 'Product Name':
              $pname = $f[1];
            break;
            case 'Serial Number':
              $serial = $f[1];
            break;
          }
      }

      if (!empty($serial)) {
          if ($s->o_pserver) {
              if ($s->o_pserver->serial != $serial) {
                  $s->o_pserver->serial = $serial;
                  $s->log("updated serial number: $serial", LLOG_INFO);
                  $s->o_pserver->update();
              }
          }
      }
      $mo = new Model();
      $mo->name = $pname;
      $mo->vendor = $vendor;
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
   * sysctl
   */
  public static function updateSysCtl(&$s)
  {
      $sysctl = $s->findBin('sysctl');
      $cmd_sysctl = "$sysctl hw.ncpu hw.model hw.physmem hw.clockrate";
      $out_sysctl = $s->exec($cmd_sysctl);

      $lines = explode(PHP_EOL, $out_sysctl);

      $memsize = $cpuspeed = $cpu = $nrcpu = $nrcore = $nrstrand = 0;

      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }

          $f = preg_split("/:/", $line);
          if (count($f) < 2) {
              continue;
          }

          $key = $f[0];
          $value = trim($f[1]);

          switch ($key) {
        case 'hw.ncpu':
      $nrcpu = $value;
    break;
        case 'hw.model':
      $cpu = $value;
    break;
        case 'hw.physmem':
      $memsize = round($value / 1024 / 1024);
    break;
        case 'hw.clockrate':
      $cpuspeed = $value.'MHz';
    break;
      }
      }

      if ($memsize && $s->data('hw:memory') != $memsize) {
          $s->setData('hw:memory', $memsize);
          $s->log('Updating Memory size: '.$memsize, LLOG_INFO);
      }

      if ($s->data('hw:nrcpu') != $nrcpu) {
          $s->setData('hw:nrcpu', $nrcpu);
          $s->log('updated hw:nrcpu => '.$nrcpu, LLOG_INFO);
      }

      if ($s->data('hw:nrcore') != $nrcore) {
          $s->setData('hw:nrcore', $nrcore);
          $s->log('updated hw:nrcore => '.$nrcore, LLOG_INFO);
      }

      if ($s->data('hw:nrstrand') != $nrstrand) {
          $s->setData('hw:nrstrand', $nrstrand);
          $s->log('updated hw:nrstrand => '.$nrstrand, LLOG_INFO);
      }

      if (strcmp($s->data('hw:cpu'), $cpu)) {
          $s->setData('hw:cpu', $cpu);
          $s->log('updated hw:cpu=> '.$cpu, LLOG_INFO);
      }

      if (strcmp($s->data('hw:cpuspeed'), $cpuspeed)) {
          $s->setData('hw:cpuspeed', $cpuspeed);
          $s->log('updated hw:cpuspeed => '.$cpuspeed, LLOG_INFO);
      }

      return 0;
  }

  /* updates function for Solaris */

  /**
   * nfs_shares
   */
  public static function updateNfsShares(&$s)
  {
      $cat = $s->findBin('cat');
      $cmd_cat = "$cat /etc/dfs/sharetab";
      $out_cat = $s->exec($cmd_cat);

      $lines = explode(PHP_EOL, $out_cat);
      $found_n = array();

      foreach ($lines as $line) {
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
          if ($changed) {
              $no->update();
          }
          $found_n[''.$no] = $no;
      }

      OSType::cleanRemoved($s, 'a_nfss', null, $found_n);

      return 0;
  }

  /**
   * nfs_mount
   */
  public static function updateNfsMounts(&$s)
  {
      $cat = $s->findBin('cat');
      $cmd_cat = "$cat /etc/mnttab";
      $out_cat = $s->exec($cmd_cat);

      $lines = explode(PHP_EOL, $out_cat);
      $found_n = array();

      foreach ($lines as $line) {
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
          if ($changed) {
              $no->update();
          }
          $found_n[''.$no] = $no;
      }

      OSType::cleanRemoved($s, 'a_nfsm', null, $found_n);

      return 0;
  }

    public static function updatePackageFBsd(&$s)
    {
        $pkg = $s->findBin('pkg');
        $cmd_pkg = "$pkg query -a %n-%v";
        $out_pkg = $s->exec($cmd_pkg);

        $lines = explode(PHP_EOL, $out_pkg);
        $found_p = array();

        $pkg = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            if (preg_match('/([^\s]*)-([^\s-]*)/', $line, $pf)) {
                $pkg = array();
                $pkg['name'] = $pf[1];
                $pkg['version'] = $pf[2];
                $pkg['status'] = 'installed';
                $pkg['fmri'] = $pf[1].','.$pf[2].'-'.':';
                $found_p[$pf[1]] = $pkg;
            }
        }

        return $found_p;
    }

  /**
   * packages
   */
  public static function updatePackages(&$s)
  {
      $found_p = OSFreeBSD::updatePackageFBsd($s);

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
          foreach ($f as $field) {
              if (isset($pkg[$field]) && $pkg[$field] != $po->{$field}) {
                  $po->{$field} = $pkg[$field];
                  $s->log("$po:$field => ".$pkg[$field], LLOG_DEBUG);
              }
          }
          $po->update();
      }
      OSType::cleanRemoved($s, 'a_pkg', 'name', $found_p);

      return 0;
  }

  /**
   * network
   */
  public static function updateNetworkFBsd(&$s)
  {
      $found_if = array();

      $ifconfig = $s->findBin('ifconfig');
      $cmd_ifconfig = "$ifconfig -a";
      $out_ifconfig = $s->exec($cmd_ifconfig);

      $lines = explode(PHP_EOL, $out_ifconfig);

      $ifname = $c_if = $c_vif = $alias = null;
      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }

          $f = preg_split("/\s+/", $line);

          if (preg_match('/^([a-z0-9:]*):$/', $f[0], $m) && !preg_match('/^media:|^status:/', $f[0])) {
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
          } elseif (!strcmp($f[0], 'zone')) {
              if (isset($found_if[$ifname])) {
                  $z = new Zone();
                  $z->fk_server = $s->id;
                  $z->name = $f[1];
                  if ($z->fetchFromFields(array('fk_server', 'name'))) {
                      $s->log("Zone added: $z", LLOG_INFO);
                      $z->insert();
                  }
                  if (isset($found_if[$ifname]['addr'][$c_vif])) {
                      $found_if[$ifname]['addr'][$c_vif]['fk_zone'] = $z->id;
                  }
              }
          } elseif (!strcmp($f[0], 'status:')) {
              $found_if[$ifname]['status'] = $f[1];
          } elseif (!strcmp($f[0], 'media:')) {
              $found_if[$ifname]['media'] = $f[1];
          }
      }

      return $found_if;
  }

    public static function updateNetwork(&$s)
    {
        $ifs = OSFreeBSD::updateNetworkFBsd($s);

        $f = array(
        'ifname',
        'fk_server',
        'alias',
        'layer',
        'fk_zone',
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

        OSType::cleanRemoved($s, 'a_net', null, $found_if);

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
   * uname -a
   */
  public static function updateUname(&$s)
  {

    /* get uname -a */
    $uname = $s->findBin('uname');
      $hw_class = trim($s->exec($uname.' -m'));
      $os_version = trim($s->exec($uname.' -r'));
      $kr_version = trim($s->exec($uname.' -K'));
      $platform = trim($s->exec($uname.' -p'));

      $s->setData('os:version', $os_version);
      $s->setData('os:kernel', $kr_version);
      $s->setData('hw:class', $hw_class);
      $s->setData('hw:platform', $platform);

      return 0;
  }

  /**
   * disk
   */
  public static function updateDisk(&$s)
  {
      $ls = $s->findBin('ls');
      $cmd_ls = "$ls /dev/dsk/*s2";
      $out_ls = $s->exec($cmd_ls);

      $lines = explode(PHP_EOL, $out_ls);
      $found_d = array();

      foreach ($lines as $line) {
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
      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }
          if (preg_match('/^(c[0-9]*t[A-Z0-9]*d[0-9]*)/', $line, $m)) {
              if ($cur_disk) {
                  $changed = false;
                  foreach ($fobj as $f) {
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
          } elseif (!$imdone && preg_match('/^Vendor: (.*) Product: (.*)Revision: (.*)Serial No:(.*)$/', $line, $m)) {
              $vars['vendor'] = trim($m[1]);
              $vars['product'] = trim($m[2]);
              $vars['rev'] = trim($m[3]);
              if (preg_match('/Size: (.*) <([0-9]*) bytes>/', trim($m[4]), $ms)) {
                  $vars['serial'] = trim($ms[1]);
                  $vars['size'] = trim($ms[2]);
              } else {
                  $vars['serial'] = preg_replace('/ Size:.*/', '', trim($m[4]));
              }
          } elseif (!$imdone && preg_match('/^Size: (.*)$/', $line, $m)) {
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
          foreach ($fobj as $f) {
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

      OSType::cleanRemoved($s, 'a_disk', 'dev', $found_d);

      return 0;
  }

  /**
   * CDP
   */
  public static function updateCdp(&$s)
  {
      $sudo = $s->findBin('sudo');
      $snoop = $s->findBin('snoop');
      $cmd_snoop = "$sudo $snoop -P -x 0 -c 1 -r -s 1600 -d %s ether dst 01:00:0c:cc:cc:cc and greater 150";

      $s->fetchRL('a_net');

      foreach ($s->a_net as $net) {
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
        if ($upd) {
            $ns->update();
        }
    }
    /* Check interface */
    if (isset($cdpp->ent['port']) && !empty($cdpp->ent['port'])) {
        if (!$ns) {
            continue;
        } // no switch...
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
   * swap
   */
  public static function updateSwap(&$s)
  {
      $swap = $s->findBin('swapinfo');
      $cmd_swap = "$swap";
      $out_swap = $s->exec($cmd_swap);

      $lines = explode(PHP_EOL, $out_swap);

      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }

          if (preg_match('/^Device/', $line)) {
              continue;
          }

          $f = preg_split("/\s+/", $line);
          $dev = $f[0];

          if (preg_match('/\/dev\/md\//', $dev)) {
              $s->log("Found swap MD: $dev", LLOG_DEBUG);
          } elseif (preg_match('/^\/dev\/zvol\//', $dev)) {
              $s->log("Found ZFS swap: $dev", LLOG_DEBUG);
          } else {
              $s->log("Found Device swap: $dev", LLOG_DEBUG);
          }
      }
  }

  /**
   * zfs
   */
  public static function updateZfs(&$s)
  {
      $zpool = $s->findBin('zpool');
      if (empty($zpool)) {
          return 0;
      }

      $cmd_zpool = "$zpool list -H -o name,size,free,capacity";
      $cmd_ozpool = "$zpool list -H -o name,size,available,capacity";
      $cmd_zhealth = "$zpool list -H -o health";

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

      foreach ($lines as $line) {
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
          $health = $s->exec($cmd_zhealth.' '.$p->name);
          $health = trim($health);
          if ($size != $p->size) {
              $p->size = $size;
              $upd = true;
              $s->log("Changed pool $p size => $size", LLOG_INFO);
          }
          if ($health != $p->status) {
              $p->status = $health;
              $upd = true;
              $s->log("Changed pool $p status => $health", LLOG_INFO);
          }
          if ($used != $p->used) {
              $p->used = $used;
              $upd = true;
              $s->log("Changed pool $p used => $used", LLOG_INFO);
          }
          if ($upd) {
              $p->update();
          }
          $found_z[$p->name] = $p;
      }
      OSType::cleanRemoved($s, 'a_pool', 'name', $found_p);

    /* update zpool devices */

    $cmd_status = "$zpool status %s";
      $zfs = $s->findBin('zfs');
      $cmd_dset = "$zfs list -H -r -o space,type,quota %s";

      foreach ($s->a_pool as $p) {
          $p->fetchJT('a_disk');
          $p->fetchRL('a_dataset');
          $cmd_s = sprintf($cmd_status, $p->name);
          $cmd_d = sprintf($cmd_dset, $p->name);
          $out_s = $s->exec($cmd_s);

          $lines = explode(PHP_EOL, $out_s);

          $vdev_list = false;
          $found_v = array();

          foreach ($lines as $line) {
              $line = trim($line);
              if (empty($line)) {
                  continue;
              }

              if (!$vdev_list && preg_match('/^NAME/', $line)) {
                  $vdev_list = true;
                  continue;
              }

              if ($vdev_list && preg_match('/^errors:/', $line)) {
                  $vdev_list = false;
                  continue;
              }

        /* @TODO: add the type of device */
        if ($vdev_list && !preg_match('/^mirror|^raid|^log|^spare|^cache/', $line)) {

      /* we should have a dev here... */
          $f = preg_split("/\s+/", $line);
            $dev = $f[0];
            if (!strcmp($dev, $p->name)) {
                continue;
            }

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
          foreach ($p->a_disk as $d) {
              if (isset($found_v[$d->dev])) {
                  continue;
              }
              $s->log("Removing disk $d from pool $p", LLOG_INFO);
              $p->delFromJT('a_disk', $d);
          }

      /* dataset indexation */
      $found_d = array();
          $out_d = $s->exec($cmd_d);
          $lines = explode(PHP_EOL, $out_d);
          foreach ($lines as $line) {
              $line = trim($line);
              if (empty($line)) {
                  continue;
              }

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
/*
# zfs list -r -o space,type,quota slc8.mgmt/test
NAME                        AVAIL   USED  USEDSNAP  USEDDS  USEDREFRESERV  USEDCHILD  TYPE        QUOTA
slc8.mgmt/test              95.7G   316M         0   63.9K              0       316M  filesystem   none
slc8.mgmt/test/test1        95.8G  18.6M         0   18.6M              0          0  filesystem   none
slc8.mgmt/test/test1@bck        -      0         -       -              -          -  snapshot        -
slc8.mgmt/test/test2        95.7G   141M     38.0K    141M              0          0  filesystem   none
slc8.mgmt/test/test2@bck        -  38.0K         -       -              -          -  snapshot        -
slc8.mgmt/test/test2-clone  95.7G  75.6M         0   75.6M              0          0  filesystem   none
*/
    $quota = $f[8];
              $type = $f[7];
              if (!strcmp($quota, "none")) {
                  $quota = 0;
              } else {
                  $quota = Pool::formatSize($quota);
              }
              $used = Pool::formatSize($f[2]);
              $usedds = Pool::formatSize($f[4]);
              switch ($type) {
          case 'snapshot':
        break;
          case 'filesystem':
      case 'volume':
          default:
        $used = $usedds;
        break;
        }
              if ($type && $do->type != $type) {
                  $upd = true;
                  $s->log("updated $do type => $type", LLOG_DEBUG);
                  $do->type = $type;
              }
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
              if ($upd) {
                  $do->update();
              }
              $found_d[$do->name] = $do;
          }
          OSType::cleanRemoved($s, 'a_dataset', 'name', $found_d);
      }
  }

  /* Screening */
  public static function htmlDump($s)
  {
      return array(
        'Version' => $s->data('os:version'),
        'Kernel' => $s->data('os:kernel'),
       );
  }

    public static function dump($s)
    {
        $ker_ver = $s->data('os:kernel');
        $os_ver = $s->data('os:version');
        if (empty($ker_ver)) {
            $ker_ver = null;
        }
        if (empty($os_ver)) {
            $os_ver = null;
        }
        $txt = '';
        $txt .= $s->o_os->name.' ';
        $txt .= ($ol_ver) ? ($sol_ver.' ') : '';
        $txt .= ($ker_ver) ? ('/ Kernel: '.$ker_ver.' ') : '';

        $s->log(sprintf("%15s: %s", 'OS', $txt), LLOG_INFO);
    }
}
