<?php
 
define('PRTCONF_HEADER', 1);
define('PRTCONF_MAIN', 2);
define('PRTCONF_SECTION', 3);
define('PRTCONF_DRV', 4);
define('PRTCONF_DISKPATHS', 5);
define('PRTCONF_DMINOR', 6);

class OSSolaris extends OSType
{
    public static $extraActions = array();

    public static $binPaths = array(
        "/bin",
        "/usr/bin",
        "/usr/local/bin",
        "/sbin",
        "/usr/sbin",
        "/usr/local/sbin",
        "/usr/ccs/sbin",
        "/opt/csw/bin",
        "/opt/csw/sbin",
    );

    protected static $_update = array(
        'Server' => array(
            "updateGroup",
            "updateUname",
            "updatePrtDiag",
            "updatePrtConf",
            "updateRelease",
            "updateSneep",
            "updateNetwork",
            "updateCpu",
            "updateHostId",
            "updateZones",
            "updatePatches",
            "updatePackages",
            "updateNfsShares",
            "updateNfsMounts",
            "updateProjects",
            "updateDisk",
        //    "updateFcInfo",
            "updateZfs",
            "updateSds",
            "updateSwap",
            "updateProcess",
        //    "updateCdp",
        //    "updateSwap",
        ),
    );

  public static function updateGroup(&$s) {
      return OSLinux::updateGroup($s);
  }

  /* Extra actions functions */
  public static function actionZFSArc(&$s)
  {
      $ret = array();
      $res = '<h3>ZFS Arc status</h3>'."\n";

      try {
          $s->connect();
          $kstat = $s->findBin('kstat');
          $kstat_keys = 'unix:0:system_pages:physmem unix:0:system_pages:freemem unix:0:system_pages:lotsfree zfs:0:arcstats:p zfs:0:arcstats:c zfs:0:arcstats:c_min zfs:0:arcstats:c_max zfs:0:arcstats:size zfs:0:arcstats:hits zfs:0:arcstats:misses zfs:0:arcstats:mfu_hits zfs:0:arcstats:mru_hits zfs:0:arcstats:mfu_ghost_hits zfs:0:arcstats:mru_ghost_hits zfs:0:arcstats:demand_data_hits zfs:0:arcstats:demand_metadata_hits zfs:0:arcstats:prefetch_data_hits zfs:0:arcstats:prefetch_metadata_hits zfs:0:arcstats:demand_data_misses zfs:0:arcstats:demand_metadata_misses zfs:0:arcstats:prefetch_data_misses zfs:0:arcstats:prefetch_metadata_misses';
          $pagesize = $s->findBin('pagesize');
          $pagesize = trim($s->exec($pagesize));
          $kstat_out = $s->exec($kstat.' -p '.$kstat_keys);
          $lines = explode(PHP_EOL, $kstat_out);
/*
zfs:0:arcstats:l2_abort_lowmem  349
zfs:0:arcstats:l2_cksum_bad     890636
zfs:0:arcstats:l2_evict_lock_retry      0
zfs:0:arcstats:l2_evict_reading 0
zfs:0:arcstats:l2_feeds 1654297
zfs:0:arcstats:l2_hdr_size      42533776
zfs:0:arcstats:l2_hits  2832237
zfs:0:arcstats:l2_io_error      0
zfs:0:arcstats:l2_misses        37939686
zfs:0:arcstats:l2_read_bytes    337036622336
zfs:0:arcstats:l2_rw_clash      0
zfs:0:arcstats:l2_size  18502909952
zfs:0:arcstats:l2_write_bytes   2063272522752
zfs:0:arcstats:l2_writes_done   376002
zfs:0:arcstats:l2_writes_error  0
zfs:0:arcstats:l2_writes_hdr_miss       0
zfs:0:arcstats:l2_writes_sent   376002
*/

      $vk = array();
          foreach ($lines as $line) {
              $line = trim($line);
              if (empty($line)) {
                  continue;
              }
              $f = preg_split("/\s+/", $line);
              if (count($f) < 2) {
                  continue; // malformed line
              }
              $name = trim($f[0]);
              $value = trim($f[1]);
              $vk[$name] = $value;
          }

          $phys_pages = Utils::getVal($vk, 'unix:0:system_pages:physmem');
          $free_pages = Utils::getVal($vk, 'unix:0:system_pages:freemem');
          $lotsfree_pages = Utils::getVal($vk, 'unix:0:system_pages:lotsfree');
          $mru_size = Utils::getVal($vk, 'zfs:0:arcstats:p');
          $target_size = Utils::getVal($vk, 'zfs:0:arcstats:c');
          $arc_min_size = Utils::getVal($vk, 'zfs:0:arcstats:c_min');
          $arc_max_size = Utils::getVal($vk, 'zfs:0:arcstats:c_max');
          $arc_size = Utils::getVal($vk, 'zfs:0:arcstats:size');
          $arc_hits = Utils::getVal($vk, 'zfs:0:arcstats:hits');
          $arc_misses = Utils::getVal($vk, 'zfs:0:arcstats:misses');
          $mfu_hits = Utils::getVal($vk, 'zfs:0:arcstats:mfu_hits');
          $mru_hits = Utils::getVal($vk, 'zfs:0:arcstats:mru_hits');
          $mfu_ghost_hits = Utils::getVal($vk, 'zfs:0:arcstats:mfu_ghost_hits');
          $mru_ghost_hits = Utils::getVal($vk, 'zfs:0:arcstats:mru_ghost_hits');
          $demand_data_hits = Utils::getVal($vk, 'zfs:0:arcstats:demand_data_hits');
          $demand_metadata_hits = Utils::getVal($vk, 'zfs:0:arcstats:demand_metadata_hits');
          $prefetch_data_hits = Utils::getVal($vk, 'zfs:0:arcstats:prefetch_data_hits');
          $prefetch_metadata_hits = Utils::getVal($vk, 'zfs:0:arcstats:prefetch_metadata_hits');
          $demand_data_misses = Utils::getVal($vk, 'zfs:0:arcstats:demand_data_misses');
          $demand_metadata_misses = Utils::getVal($vk, 'zfs:0:arcstats:demand_metadata_misses');
          $prefetch_data_misses = Utils::getVal($vk, 'zfs:0:arcstats:prefetch_data_misses');
          $prefetch_metadata_misses = Utils::getVal($vk, 'zfs:0:arcstats:prefetch_metadata_misses');

      /* calculations */
      $phys_memory = ($phys_pages * $pagesize);
          $free_memory = ($free_pages * $pagesize);
          $lotsfree_memory = ($lotsfree_pages * $pagesize);

          $mfu_size = $target_size - $mru_size;
          $mru_perc = 100*($mru_size / $target_size);
          $mfu_perc = 100*($mfu_size / $target_size);

          $arc_accesses_total = ($arc_hits + $arc_misses);
          $arc_hit_perc = 100*($arc_hits / $arc_accesses_total);
          $arc_miss_perc = 100*($arc_misses / $arc_accesses_total);

          $anon_hits = $arc_hits - ($mfu_hits + $mru_hits + $mfu_ghost_hits + $mru_ghost_hits);
          $real_hits = ($mfu_hits + $mru_hits);
          $real_hits_perc = 100*($real_hits / $arc_accesses_total);

          $anon_hits_perc = 100*($anon_hits / $arc_hits);
          $mfu_hits_perc = 100*($mfu_hits / $arc_hits);
          $mru_hits_perc = 100*($mru_hits / $arc_hits);
          $mfu_ghost_hits_perc = 100*($mfu_ghost_hits / $arc_hits);
          $mru_ghost_hits_perc = 100*($mru_ghost_hits / $arc_hits);

          $demand_data_hits_perc = 100*($demand_data_hits / $arc_hits);
          $demand_metadata_hits_perc = 100*($demand_metadata_hits / $arc_hits);
          $prefetch_data_hits_perc = 100*($prefetch_data_hits / $arc_hits);
          $prefetch_metadata_hits_perc = 100*($prefetch_metadata_hits / $arc_hits);

          $demand_data_misses_perc = 100*($demand_data_misses / $arc_misses);
          $demand_metadata_misses_perc = 100*($demand_metadata_misses / $arc_misses);
          $prefetch_data_misses_perc = 100*($prefetch_data_misses / $arc_misses);
          $prefetch_metadata_misses_perc = 100*($prefetch_metadata_misses / $arc_misses);
          $prefetch_data_total = ($prefetch_data_hits + $prefetch_data_misses);
          $prefetch_data_perc = "00";
          if ($prefetch_data_total > 0) {
              $prefetch_data_perc = 100*($prefetch_data_hits / $prefetch_data_total);
          }
          $demand_data_total = ($demand_data_hits + $demand_data_misses);
          $demand_data_perc = 100*($demand_data_hits / $demand_data_total);

          $res .= '<h4>Graphs</h4>';
          $res .= '<div class="row">';
          $res .= '<div class="col-sm-6"><h6 class="text-center">ZFS Cache Hits</h6>';
          $res .= '<div id="pieCacheHits"></div>';
          $res .= '</div>';
          $res .= '<div class="col-sm-6"><h6 class="text-center">ZFS Cache Hits Demand Data</h6>';
          $res .= '<div id="pieCacheHitsDT"></div>';
          $res .= '</div>';
          $res .= '</div>';
          $res .= '<div class="row">';
          $res .= '<div class="col-sm-6"><h6 class="text-center">ZFS Cache Misses Demand Data</h6>';
          $res .= '<div id="pieCacheMissDT"></div>';
          $res .= '</div>';
          $res .= '</div>';

          $pie = array();
          $pie['pieCacheHits'] = array();
          $pie['pieCacheHits']['Anon'] = $anon_hits;
          $pie['pieCacheHits']['MRU'] = $mru_hits;
          $pie['pieCacheHits']['MFU'] = $mfu_hits;
          $pie['pieCacheHits']['MRU Ghost'] = $mru_ghost_hits;
          $pie['pieCacheHits']['MFU Ghost'] = $mfu_ghost_hits;
          $pie['pieCacheHitsDT'] = array();
          $pie['pieCacheHitsDT']['Demand data'] = $demand_data_hits;
          $pie['pieCacheHitsDT']['Demand Metadata'] = $demand_metadata_hits;
          $pie['pieCacheHitsDT']['Prefetch data'] = $prefetch_data_hits;
          $pie['pieCacheHitsDT']['Prefetch Metadata'] = $prefetch_metadata_hits;
          $pie['pieCacheMissDT'] = array();
          $pie['pieCacheMissDT']['Demand data'] = $demand_data_misses;
          $pie['pieCacheMissDT']['Demand Metadata'] = $demand_metadata_misses;
          $pie['pieCacheMissDT']['Prefetch Data'] = $prefetch_data_misses;
          $pie['pieCacheMissDT']['Prefetch Metadata'] = $prefetch_metadata_misses;

          $res .= '<h4>System memory</h4>';
          $res .= '<ul>';
          $res .= sprintf("<li>Physical RAM: \t%d MB</li>", $phys_memory / 1024 / 1024);
          $res .= sprintf("<li>Free Memory : \t%d MB</li>", $free_memory / 1024 / 1024);
          $res .= sprintf("<li>LotsFree: \t%d MB</li>", $lotsfree_memory / 1024 / 1024);
          $res .= '</ul>';

          $res .= '<h4>ARC Size</h4>';
          $res .= '<ul>';
          $res .= sprintf("<li>Current Size:             %d MB (arcsize)</li>\n", $arc_size / 1024 / 1024);
          $res .= sprintf("<li>Target Size (Adaptive):   %d MB (c)</li>\n", $target_size / 1024 / 1024);
          $res .= sprintf("<li>Min Size (Hard Limit):    %d MB (zfs_arc_min)</li>\n", $arc_min_size / 1024 / 1024);
          $res .= sprintf("<li>Max Size (Hard Limit):    %d MB (zfs_arc_max)</li>\n", $arc_max_size / 1024 / 1024);
          $res .= '</ul>';

          $res .= '<h4>ARC Size Breakdown</h4>';
          $res .= '<ul>';
          $res .= sprintf("<li>Most Recently Used Cache Size: \t %2d%% \t%d MB (p)</li>\n", $mru_perc, $mru_size / 1024 / 1024);
          $res .= sprintf("<li>Most Frequently Used Cache Size: \t %2d%% \t%d MB (c-p)</li>\n", $mfu_perc, $mfu_size / 1024 / 1024);
          $res .= '</ul>';

          $res .= '<h4>ARC Efficiency</h4>';
          $res .= '<ul>';
          $res .= sprintf("<li>Cache Access Total:        \t %d</li>\n", $arc_accesses_total);
          $res .= sprintf("<li>Cache Hit Ratio:      %2d%%\t %d   \t[Defined State for buffer]</li>\n", $arc_hit_perc, $arc_hits);
          $res .= sprintf("<li>Cache Miss Ratio:     %2d%%\t %d   \t[Undefined State for Buffer]</li>\n", $arc_miss_perc, $arc_misses);
          $res .= sprintf("<li>REAL Hit Ratio:       %2d%%\t %d   \t[MRU/MFU Hits Only]</li>\n", $real_hits_perc, $real_hits);
          $res .= sprintf("<li>Data Demand   Efficiency:    %2d%%</li>\n", $demand_data_perc);
          if ($prefetch_data_total == 0) {
              $res .= sprintf("<li>Data Prefetch Efficiency:    DISABLED (zfs_prefetch_disable)</li>\n");
          } else {
              $res .= sprintf("<li>Data Prefetch Efficiency:    %2d%%</li>\n", $prefetch_data_perc);
          }

          $res .= sprintf("<li>CACHE HITS BY CACHE LIST:\n");
          $res .= '<ul>';
          if ($anon_hits < 1) {
              $res .= sprintf("<li> Anon:                       --%% \t Counter Rolled.</li>\n");
          } else {
              $res .= sprintf("<li> Anon:                       %2d%% \t %d            \t[ New Customer, First Cache Hit ]</li>\n", $anon_hits_perc, $anon_hits);
          }
          $res .= sprintf("<li> Most Recently Used:         %2d%% \t %d (mru)      \t[ Return Customer ]</li>\n", $mru_hits_perc, $mru_hits);
          $res .= sprintf("<li> Most Frequently Used:       %2d%% \t %d (mfu)      \t[ Frequent Customer ]</li>\n", $mfu_hits_perc, $mfu_hits);
          $res .= sprintf("<li> Most Recently Used Ghost:   %2d%% \t %d (mru_ghost)\t[ Return Customer Evicted, Now Back ]</li>\n", $mru_ghost_hits_perc, $mru_ghost_hits);
          $res .= sprintf("<li> Most Frequently Used Ghost: %2d%% \t %d (mfu_ghost)\t[ Frequent Customer Evicted, Now Back ]</li>\n", $mfu_ghost_hits_perc, $mfu_ghost_hits);
          $res .= '</ul></li>';

          $res .= sprintf("<li>CACHE HITS BY DATA TYPE:\n");
          $res .= sprintf("<li>  Demand Data:                %2d%% \t %d </li>\n", $demand_data_hits_perc, $demand_data_hits);
          $res .= sprintf("<li>  Prefetch Data:              %2d%% \t %d </li>\n", $prefetch_data_hits_perc, $prefetch_data_hits);
          $res .= sprintf("<li>  Demand Metadata:            %2d%% \t %d </li>\n", $demand_metadata_hits_perc, $demand_metadata_hits);
          $res .= sprintf("<li>  Prefetch Metadata:          %2d%% \t %d </li>\n", $prefetch_metadata_hits_perc, $prefetch_metadata_hits);
          $res .= '</ul></li>';

          $res .= sprintf("<li>CACHE MISSES BY DATA TYPE:\n");
          $res .= sprintf("<li>  Demand Data:                %2d%% \t %d </li>\n", $demand_data_misses_perc, $demand_data_misses);
          $res .= sprintf("<li>  Prefetch Data:              %2d%% \t %d </li>\n", $prefetch_data_misses_perc, $prefetch_data_misses);
          $res .= sprintf("<li>  Demand Metadata:            %2d%% \t %d </li>\n", $demand_metadata_misses_perc, $demand_metadata_misses);
          $res .= sprintf("<li>  Prefetch Metadata:          %2d%% \t %d </li>\n", $prefetch_metadata_misses_perc, $prefetch_metadata_misses);
          $res .= '</ul></li>';

          $res .= '</ul>';

          $s->disconnect();
      } catch (Exception $e) {
          $res .= '<p>'.$e.'</p>'."\n";
      }

      $ret['html'] = $res;
      $ret['pie'] = $pie;

      return $ret;
  }

  /* updates function for Solaris */
   private static function prtconf_parse($buf) {

      $ret = array();
      $stack = array();
      $lines = explode(PHP_EOL, $buf);
      $state = PRTCONF_HEADER;
      $instance = -1;
      $ncur = 0;
      $curptr2 = $curptr = null;
      $pathnr = null;
      $pathdev = null;

      for ($i=0; $i<count($lines); $i++) {
          $line = $lines[$i];
          if (empty($line)) { 
              continue; 
          }
          $n = strspn($line, ' ') / 4; /* prtconf sucks, there's no tabs but spaces! grmbl */
          $line = trim($line);
          switch($state) {
              case PRTCONF_HEADER:
                  if (preg_match('/^System Peripherals .*:$/', $line)) {
                      $state = PRTCONF_MAIN;
                  } else if (preg_match('/^([^:]+):(.+)$/', $line, $m)) {
                      $name = trim($m[1]);
                      $value = trim($m[2]);
                      $ret[$name] = $value;
                  }
                  break;
              case PRTCONF_MAIN:
                  if (!preg_match('/:/', $line)) {
                      $ret[$line] = array();
                      $stack[$n] = &$ret[$line];
                      $state = PRTCONF_DRV;
                  }
                  break;
              case PRTCONF_DRV:
                  if (preg_match('/^([^,]+), instance #([0-9]+)$/', $line, $m)) {
                      $drv = $m[1];
                      $instance = $m[2];
                      if (!isset($stack[$n-1][$drv])) {
                          $stack[$n-1][$drv] = array();
                          $stack[$n] = &$stack[$n-1][$drv];
                      }
                      $stack[$n][$instance] = array();
                      $ncur = $n;
                  } else if (preg_match("/^name='([^']+)' type=([^ ]+)/", $line, $m)) {
                      $name = $m[1];
                      $type = $m[2];
                      $dev = null;
                      $values = null;
                      $value = null;
                      $nitems = null;
                      /* see if there is a dev */
                      if (preg_match("/dev=([^ ]+)/", $line, $d)) {
                          $dev = $d[1];
                      }
                      /* next line contains the items */
                      if (preg_match("/items=([^ ]+)/", $line, $n)) {
                          $nitems = $n[1];
                          if ($nitems > 0) {
                              $i++;
                              $line = $lines[$i];
                              $line = trim($line);
                              if (preg_match('/^value=(.*)$/', $line, $m)) {
                                  $value = $m[1];
                              }
                          }
                      }
                      switch($type) {
                          case 'string':
                              $value = trim($value, "'");
                              $values = preg_split("/' \+ '/", $value);
                              break;
                          case 'byte':
                          case 'unknown':
                          case 'int64':
                          case 'int':
                              $values = explode('.', $value);
                              break;
                          case 'boolean':
                          default:
                              break;
                      }
                      if ($instance == -1) {
                          $stack[$ncur][$name] = array();
                          $curptr = &$stack[$ncur][$name];
                      } else {
                          $stack[$ncur][$instance][$name] = array();
                          $curptr = &$stack[$ncur][$instance][$name];
                      }
                      $curptr['type'] = $type;
                      if ($nitems) $curptr['count'] = $nitems;
                      if ($values) $curptr['items'] = $values;
                      if ($dev) $curptr['dev'] = $dev;

                  } else if (preg_match('/^([^:]+): (.*)$/', $line, $m)) {
                      $name = trim($m[1]);
                      $value = trim($m[2]);
                      if ($instance == -1) {
                          $stack[$ncur][$name] = $value;
                      } else {
                          $stack[$ncur][$instance][$name] = $value;
                      }
                  } else if (preg_match('/^Paths from multipath bus adapters:$/', $line)) {
                      $state = PRTCONF_DISKPATHS;
                      $pathnr = null;
                      $pathdev = null;
                      if ($instance == -1) {
                          $stack[$ncur]['paths'] = array();
                          $curptr = &$stack[$ncur]['paths'];
                      } else {
                          $stack[$ncur][$instance]['paths'] = array();
                          $curptr = &$stack[$ncur][$instance]['paths'];
                      }

                  } else if (preg_match('/:$/', $line)) { /* Section title, skip */
                      continue;
                  }
                  break;
              case PRTCONF_DISKPATHS:
                  /*
                   * mpt_sas#12 (online)
                   * Device Minor Nodes:
                   */
                  if (preg_match('/^Path ([0-9]+): (.*)$/', $line, $m)) {
                      $pathnr = $m[1];
                      $pathdev = $m[2];
                      $curptr[$pathnr] = array();
                      $curptr[$pathnr]['dev'] = $pathdev;
                  } else if (preg_match('/^([^#])#([0-9]+) \(([^)]+)\)$/', $line, $m)) {
                      $curptr[$pathnr]['controller'] = $m[1].'#'.$m[2];
                      $curptr[$pathnr]['state'] = $m[3];
                  } else if (preg_match('/^Device Minor Nodes:$/', $line)) {
                      $state = PRTCONF_DMINOR;
                  } else if (preg_match("/^name='([^']+)' type=([^ ]+)/", $line, $m)) {
                      $name = $m[1];
                      $type = $m[2];
                      $dev = null;
                      $values = null;
                      $value = null;
                      $nitems = null;
                      /* see if there is a dev */
                      if (preg_match("/dev=([^ ]+)/", $line, $d)) {
                          $dev = $d[1];
                      }
                      /* next line contains the items */
                      if (preg_match("/items=([^ ]+)/", $line, $n)) {
                          $nitems = $n[1];
                          if ($nitems > 0) {
                              $i++;
                              $line = $lines[$i];
                              $line = trim($line);
                              if (preg_match('/^value=(.*)$/', $line, $m)) {
                                  $value = $m[1];
                              }
                          }
                      }
                      switch($type) {
                          case 'string':
                              $value = trim($value, "'");
                              $values = preg_split("/' \+ '/", $value);
                              break;
                          case 'byte':
                          case 'unknown':
                          case 'int64':
                          case 'int':
                              $values = explode('.', $value);
                              break;
                          case 'boolean':
                          default:
                              break;
                      }
                      $curptr[$pathnr][$name] = array();
                      $curptr2 = &$curptr[$pathnr][$name];
                      $curptr2['type'] = $type;
                      if ($nitems) $curptr2['count'] = $nitems;
                      if ($values) $curptr2['items'] = $values;
                      if ($dev) $curptr2['dev'] = $dev;

                  }
                  break;
              case PRTCONF_DMINOR:
                  if (preg_match('/^([^,]+), instance #([0-9]+)$/', $line, $m)) {
                      $i--;
                      $state = PRTCONF_DRV;
                  }
                  break;
          }
      }
      return $ret;
  }

  private static function prtconf_disk($pc) {
      $ret = array();
      if (is_array($pc) && array_key_exists('disk', $pc)) {
          $ret = $pc['disk'];
      } else if (is_array($pc)) {
          foreach($pc as $item) {
              $ret = array_merge($ret, OSSolaris::prtconf_disk($item));
          }
      }
      return $ret;
  }


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

    public static function updatePackagesS10(&$s)
    {
        $pkginfo = $s->findBin('pkginfo');
        $cmd_pkginfo = "$pkginfo -l";
        $out_pkginfo = $s->exec($cmd_pkginfo, null, 600); /* this command can take time */

    $lines = explode(PHP_EOL, $out_pkginfo);
        $found_p = array();

        $pkg = null;
        foreach ($lines as $line) {
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

    public static function updatePackagesS11(&$s)
    {
        $pkg = $s->findBin('pkg');
        $cmd_pkg = "$pkg list -H -v";
        $out_pkg = $s->exec($cmd_pkg);

        $lines = explode(PHP_EOL, $out_pkg);
        $found_p = array();

        $pkg = null;
        foreach ($lines as $line) {
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
  public static function updatePackages(&$s)
  {
      if ($s->data('os:major') > 10) {
          $found_p = OSSolaris::updatePackagesS11($s);
      } else {
          $found_p = OSSolaris::updatePackagesS10($s);
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
   * patches
   */
  public static function updatePatches(&$s)
  {
      if ($s->data('os:major') > 10) {
          return 0; /* no more patch with solaris > 10 */
      }

      $showrev = $s->findBin('showrev');
      $cmd_showrev = "$showrev -p";
      $out_showrev = $s->exec($cmd_showrev);

      $lines = explode(PHP_EOL, $out_showrev);
      $found_p = array();

      foreach ($lines as $line) {
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

      OSType::cleanRemoved($s, 'a_patch', 'patch', $found_p);

      return 0;
  }

  /**
   * zones
   */
  public static function updateZones(&$s)
  {

    /* get hostid */
    $zoneadm = $s->findBin('zoneadm');

      $cmd_zoneadm = "$zoneadm list -pc";
      $out_zoneadm = $s->exec($cmd_zoneadm);

      $lines = explode(PHP_EOL, $out_zoneadm);
      $found_z = array();

      foreach ($lines as $line) {
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
              $s->log("updated $u infos about zone $z", LLOG_INFO);
              $z->update();
          }
          $found_z[$z->name] = $z;
      }

      OSType::cleanRemoved($s, 'a_zone', 'name', $found_z);

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

  /**
   * cpu info
   */
  public static function updateCpu(&$s)
  {
      $psrinfo = $s->findBin('psrinfo');
      $cmd_psrinfo = "$psrinfo -pv";
      $out_psrinfo = $s->exec($cmd_psrinfo);

      $lines = explode(PHP_EOL, $out_psrinfo);

      $nrcpu = $nrcore = $nrstrand = 0;

      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }

          if (preg_match('/^The physical processor has ([0-9]*) cores and ([0-9]*) virtual processor.*/', $line, $cpu)) {
              $nrcpu++;
              $nrcore += $cpu[1];
              $nrstrand += $cpu[2];
          } elseif (preg_match('/^The physical processor has ([0-9]*) virtual processor.*/', $line, $cpu)) {
              $nrcpu++;
              $nrcore += 1;
              $nrstrand += $cpu[1];
          }
          $last_line = $line;
      }

      if (preg_match('/@/', $last_line)) {
          $f_speed = explode('@', $last_line);
          $cpuspeed = trim($f_speed[1]);
      /* just fix formatting of the cpu type */
      $f_cpu = explode(' ', trim($f_speed[0]));
          $cpu = '';
          foreach ($f_cpu as $f) {
              $cpu .= $f.' ';
          }
          $cpu = trim($cpu);
      } else {
          /* just fix formatting of the cpu type */
      $f_cpu = explode(' ', trim($last_line));
          $cpu = '';
          foreach ($f_cpu as $f) {
              $cpu .= $f.' ';
          }
          $cpu = trim($cpu);
          $cpuspeed = 'unknown';
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

  /**
   * network
   */
  public static function updateNetworkS10(&$s)
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
          }
      }

      return $found_if;
  }

    public static function updateNetworkS11(&$s)
    {
        $found_if = array();
        $dladm = $s->findBin('dladm');
        $cmd_dladm = "$dladm show-phys -m";
        $out_dladm = $s->exec($cmd_dladm);

        $lines = explode(PHP_EOL, $out_dladm);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $f = preg_split("/\s+/", $line);
            if (count($f) != 5 || $f[0] == 'LINK') {
                continue;
            }
            $pnet = array();
            $pnet['ifname'] = $f[0];
            $pnet['layer'] = 2;
            $pnet['fk_server'] = $s->id;
            $pnet['address'] = $f[2];
      /* Address hereunder... */
      $pnet['addr'] = array();
            $pnet['caddr'] = 0;
            $found_if[$f[0]] = $pnet;
        }

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
          $c_if = &$found_if[$ifname];
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
            /* Address hereunder... */
            $if['addr'] = array();
          $if['caddr'] = 0;
          $found_if[$ifname] = $if;
      }
                }
            } elseif (!strcmp($f[0], 'inet') && strcmp($f[1], '0.0.0.0') && $f[1] != 0) {
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
                if (isset($found_if[$ifname])) {
                    $c_vif = $found_if[$ifname]['caddr'];
                    $found_if[$ifname]['addr'][$c_vif] = $vif;
                    $found_if[$ifname]['caddr']++;
                }
            } elseif (!strcmp($f[0], 'groupname')) {
                if (isset($found_if[$ifname])) {
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
            }
        }

        return $found_if;
    }

    public static function updateNetwork(&$s)
    {
        $major = $s->data('os:major');
        if (empty($major) || !is_numeric($major) || $major > 10) {
            $ifs = OSSolaris::updateNetworkS11($s);
        } else {
            $ifs = OSSolaris::updateNetworkS10($s);
        }

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
   * sneep
   */
  public static function updateSneep(&$s)
  {

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
                  $s->log("updated serial number: $out_sneep", LLOG_INFO);
                  $s->o_pserver->update();
              }
          }
      }

      return 0;
  }

  /**
   * cat /etc/release
   */
  public static function updateRelease(&$s)
  {

      /* get cat */
      $cat = $s->findBin('cat');

      $cmd_cat = "$cat /etc/release";
      $out_cat = $s->exec($cmd_cat);

      $release_lines = explode(PHP_EOL, $out_cat);
      $release = $release_lines[0];
      $f_release = explode(' ', $release);
      if (count($f_release) < 3) {
          $s->log('[!] Invalid /etc/release format detected, aborting...', LLOG_ERR);
          return;
      }
      $release_major = $f_release[1];
      $release_update = $f_release[2];
      if ($release_major == 'Solaris' || $f_release[0] == 'Oracle') {
          $release_major = $f_release[2];
          $release_update = $f_release[3];
      }

      if (!strncmp($release_major, "11.", 3)) {
          $pkg = $s->findBin('pkg');
          $cmd_entire = "$pkg info entire";
          $out_entire = $s->exec($cmd_entire);
          $entire_lines = explode(PHP_EOL, $out_entire);
          foreach ($entire_lines as $line) {
              $line = trim($line);
              if (preg_match('/^Branch:/', $line)) {
                  $f_branch = explode(' ', $line);
                  $branch = $f_branch[1];
                  $release_update = $branch;
                  break;
              }
          }
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
  public static function updatePrtConf(&$s)
  {
      if ($s->data('hw:cpu') == 'sparc') {
          return 0;
      }

      /* get prtconf */
      $prtconf = $s->findBin('prtconf');

      $cmd_prtconf = "$prtconf -vvv";
      $out_prtconf = $s->exec($cmd_prtconf);

      $memsize = 0;

      $pc = OSSolaris::prtconf_parse($out_prtconf);

      if (isset($pc['Memory size'])) {
          $f_mem = explode(' ', $pc['Memory size']);
          $memsize = $f_mem[0];
      }

      if ($memsize && $s->data('hw:memory') != $memsize) {
          $s->setData('hw:memory', $memsize);
          $s->log('Updating Memory size: '.$memsize, LLOG_INFO);
      }

      /*
       * We can get a lot of infos on disks using prtconf,
       * let's try to correlate detected disks and the ones
       * found in prtconf...
       */
      $disks = OSSolaris::prtconf_disk($pc);
      $s->fetchRL('a_disk');
      foreach($disks as $disk) {
        $serial = $devid = $class = $vendor = $product = $rev = $guid = $location = null;
        foreach (array('inquiry-serial-no' => 'serial', 
                     'devid' => 'devid', 
                     'class' => 'class', 
                     'inquiry-vendor-id' => 'vendor', 
                     'inquiry-product-id' => 'product', 
                     'inquiry-revision-id' => 'rev', 
                     'client-guid' => 'guid') as $k => $v) {
          if (array_key_exists($k, $disk)) {
              ${$v} = $disk[$k]['items'][0];
          } else {
              ${$v} = null;
          }
        }
        if (array_key_exists('location', $disk)) {
          $location = $disk['location'];
        }
        foreach($s->a_disk as $sd) {
            if (!strcmp($sd->guidFromDev(), $guid)) {
                $mod = false;
                if ($serial && strcmp($serial, $sd->serial)) {
                    $sd->serial = $serial;
                    $mod = true;
                    $s->log('Updated '.$sd->dev.' serial: '.$sd->serial, LLOG_INFO);
                }
                if ($class && strcmp($class, $sd->class)) {
                    $sd->class = $class;
                    $mod = true;
                    $s->log('Updated '.$sd->dev.' class: '.$sd->class, LLOG_INFO);
                }
                if ($product && strcmp($product, $sd->product)) {
                    $sd->product = $product;
                    $mod = true;
                    $s->log('Updated '.$sd->dev.' product: '.$sd->product, LLOG_INFO);
                }
                if ($rev && strcmp($rev, $sd->rev)) {
                    $sd->rev = $rev;
                    $mod = true;
                    $s->log('Updated '.$sd->dev.' rev: '.$sd->rev, LLOG_INFO);
                }
                if ($vendor && strcmp($vendor, $sd->vendor)) {
                    $sd->vendor = $vendor;
                    $mod = true;
                    $s->log('Updated '.$sd->dev.' vendor: '.$sd->vendor, LLOG_INFO);
                }
                if ($location && strcmp($location, $sd->location)) {
                    $sd->location = $location;
                    $mod = true;
                    $s->log('Updated '.$sd->dev.' location: '.$sd->location, LLOG_INFO);
                }
                if ($mod) {
                    $sd->update();
                }
            }
        }
 
      }

      return 0;
  }

  /**
   * prtdiag
   */
  public static function updatePrtDiag(&$s)
  {

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
      foreach ($prtdiag_lines as $line) {
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
  public static function updateUname(&$s)
  {

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
      $platform = $f_uname[count($f_uname) - 1];

      $s->setData('os:version', $os_version);
      $s->setData('os:kernel', $kr_version);
      $s->setData('hw:class', $hw_class);
      $s->setData('hw:platform', $platform);

      return 0;
  }

  /**
   * uname -a
   */
  public static function updateProjects(&$s)
  {
      $cat = $s->findBin('cat');
      $cmd_cat = "$cat /etc/project";
      $out_cat = $s->exec($cmd_cat);

      $found_p = array();

      $lines = explode(PHP_EOL, $out_cat);

      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line) || preg_match('/^#/', $line)) {
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
          if ($changed) {
              $po->update();
          }

          $found_p[$po->prjid] = $po;
          $s->a_prj[] = $po;
      }

      OSType::cleanRemoved($s, 'a_prj', 'prjid', $found_p);
  }

  /**
   * fcinfo
   */
  public static function updateFcInfo(&$s)
  {

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
        case 'HBA Port WWN':
      if ($cur_hba) {
          if ($changed) {
              $cur_hba->update();
          }
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
      if ($changed && $cur_hba) {
          $cur_hba->update();
      }

      OSType::cleanRemoved($s, 'a_hba', 'wwn', $found_hba);

    /* update luns */
    $found_lun = array();

      foreach ($s->a_hba as $hba) {
          $s->log("Updating hba $hba", LLOG_INFO);
          $cmd_fcinfo = "$sudo $fcinfo remote-port -sl -p ".$hba->wwn;
          $out_fcinfo = $s->exec($cmd_fcinfo);

          $lines = explode(PHP_EOL, $out_fcinfo);
          $cur_lun = null;

          foreach ($lines as $line) {
              $line = trim($line);
              if (empty($line)) {
                  continue;
              }
              $f = explode(':', $line, 2);
              if (count($f) != 2) {
                  continue;
              }
              switch ($f[0]) {
      case 'LUN':
            if ($cur_lun) {
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
      foreach ($found_lun as $lun) {
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
      } elseif (!strcmp($do->drv, 'MPxIO')) {
          $mpxio = true;
      }

          if (isset($lun['vendor']) && !empty($lun['vendor'])) {
              switch ($lun['vendor']) {
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

          if ($upd) {
              $do->update();
          }
      }

      foreach ($run_vendors as $v => $k) {
          if (!$k) {
              continue;
          }

          $s->log("Found $v, trying to run specific routine...", LLOG_INFO);

          try {
              switch ($v) {
      case 'HP':
        OSSolaris::updateDiskHp($s);
      break;
      case 'EMC':
        OSSolaris::updateDiskEmc($s);
      break;
      case 'EMC_MPXIO':
        OSSolaris::updateDiskEmcMpxIO($s);
          break;
        }
          } catch (Exception $e) {
              $s->log('Exception caught: '.$e, LLOG_ERR);
          }
      }

      return 0;
  }

    public static function updateDiskHp(&$s)
    {
        $sudo = $s->findBin('sudo');
        $xpinfo = $s->findBin('xpinfo');
        $cmd_xpinfo = "$sudo $xpinfo -d";
        $out_xpinfo = $s->exec($cmd_xpinfo);

        echo "$out_xpinfo\n";

        return 0;
    }

    public static function updateDiskEmcMpxIO(&$s)
    {
        $paths = OSSolaris::$binPaths;
        $paths[] = '/opt/emc/SYMCLI/bin';
        $paths[] = '/usr/symcli/bin';

        $sudo = $s->findBin('sudo');
        $syminq = $s->findBin('syminq', $paths);
        $cmd_syminq = "$sudo $syminq -symmids";
        $out_syminq = $s->exec($cmd_syminq);

        $lines = explode(PHP_EOL, $out_syminq);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

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
                if ($upd) {
                    $do->update();
                }
            }
        }

        return 0;
    }

    public static function updateDiskEmc(&$s)
    {
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
              $s->a_disk[] = $do;
          }
          $found_d[$dname] = $do;
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
   * sds
   */
  public static function updateSds(&$s)
  {
/*
     $metastat = $s->findBin('metastat');
      $cmd_metastat = "$metastat -p";
    $out_metastat = $s->exec($cmd_metastat);
d9 -m d29 1
d29 1 1 /dev/dsk/emcpower59a
d8 -m d28 1
d28 1 1 /dev/dsk/emcpower12a
d6 -m d26 1
d26 1 1 /dev/dsk/emcpower60b
d2 -m d22 d12 1
d22 1 1 c1t0d0s0
d12 1 1 c0t0d0s0
d3 -m d23 d13 1
d23 1 1 c1t0d0s3
d13 1 1 c0t0d0s3
d4 -m d14 d24 1
d14 1 1 c0t0d0s5
d24 1 1 c1t0d0s5
d110 -m d112 1
d112 1 1 /dev/dsk/emcpower18a
d100 -m d101 1
d101 1 1 /dev/dsk/emcpower58a

  $metaset = $s->findBin('metaset');
  $cmd_metaset = "$metaset";
*/
  }

  /**
   * swap
   */
  public static function updateSwap(&$s)
  {
      $swap = $s->findBin('swap');
      $cmd_swap = "$swap -l";
      $out_swap = $s->exec($cmd_swap);

      $lines = explode(PHP_EOL, $out_swap);

      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }

          if (preg_match('/^swapfile/', $line)) {
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

  public static function getZpoolList(&$s) {

      $zpool = $s->findBin('zpool');

      if ($s->o_os && !strcmp($s->o_os->name, 'Linux')) {
          /* if OS is Linux, default zpool commands using sudo */
          $sudo = $s->findBin('sudo');
          $zpool = $sudo.' '.$zpool;
          $s->log('[+] (OSSolaris::getZpoolList) Detected ZFS On Linux, using sudo for any zfs commands...', LLOG_INFO);
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
          $p->type = 'ZFS';
          $p->fk_server = $s->id;
          $p->name = $name;
          $upd = false;
          if ($p->fetchFromFields(array('fk_server', 'name', 'type'))) {
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
      return $found_z;
  }

  public static function getZpoolDisks(&$s, &$p) {
      $zpool = $s->findBin('zpool');
      $cmd_status = "$zpool status %s";
      $role = '';

      if ($s->o_os && !strcmp($s->o_os->name, 'Linux')) {
          /* if OS is Linux, default zpool commands using sudo */
          $sudo = $s->findBin('sudo');
          $zpool = $sudo.' '.$zpool;
          $s->log('[+] (OSSolaris::getZpoolDisks) Detected ZFS On Linux, using sudo for any zfs commands...', LLOG_INFO);
      }

      $p->fetchJT('a_disk');
      $p->fetchRL('a_dataset');
      $cmd_s = sprintf($cmd_status, $p->name);
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

          if ($vdev_list && preg_match('/^mirror|^raid|^log|^spare|^cache/', $line)) {
              $f = preg_split("/\s+/", $line);
              if (!strcmp($role, 'logs') && preg_match('/^mirror/', $f[0])) {
                  continue;
              } // skip this case
              $role = $f[0];
          } elseif ($vdev_list && !preg_match('/^mirror|^raid|^log|^spare|^cache/', $line)) {

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
              $do->role[''.$p] = $role;
              if ($do->fetchFromFields(array('fk_server', 'dev'))) {
                  $s->log("Disk $do was not found on $s for pool $p", LLOG_ERR);
                  continue;
              }

              if (!$p->isInJT('a_disk', $do, array('slice', 'role'))) {
                  if ($p->isInJT('a_disk', $do)) {
                      $s->log("changed $do slice $slice/$role to $p, deleting first", LLOG_INFO);
                      $p->delFromJT('a_disk', $do);
                  }
                  $s->log("add $do slice $slice/$role to $p", LLOG_INFO);
                  $p->addToJT('a_disk', $do);
              }
              $found_v[$do->dev] = $do;
              continue;
          } else {
              $s->log('[!] Unknown disk kind found: '.$line, LLOG_INFO);
          }
      }
      return $found_v;
  }

  public static function getZpoolDatasets(&$s, &$p) {
      $zfs = $s->findBin('zfs');

      if ($s->o_os && !strcmp($s->o_os->name, 'Linux')) {
          /* if OS is Linux, default zpool commands using sudo */
          $sudo = $s->findBin('sudo');
          $zfs = $sudo.' '.$zfs;
          $s->log('[+] (OSSolaris::getZpoolDatasets) Detected ZFS On Linux, using sudo for any zfs commands...', LLOG_INFO);
      }

      $cmd_dset = "$zfs list -H -r -o space,type,quota,reservation,compressratio,creation,origin %s";
      $cmd_d = sprintf($cmd_dset, $p->name);

      $found_d = array();

      $out_d = $s->exec($cmd_d);
      $lines = explode(PHP_EOL, $out_d);

      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }

          $f = preg_split("/\t/", $line);
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
          $quota = $f[8];
          $reserved = $f[9];
          if (preg_match('/([0-9\.]*)x/', $f[10], $m)) {
              $compressratio = $m[1];
          } else {
              $compressratio = null;
          }

          $sd = -1;
          if (isset($f[11])) {
              $sd = strtotime($f[11]);
          }

          $origin = '';
          if (isset($f[12])) {
              if (strcmp($origin, '-')) {
                  $origin = $f[12];
              }
          }

          $type = $f[7];
          if (!strcmp($reserved, "none")) {
              $reserved = 0;
          } else {
              $reserved = Pool::formatSize($reserved);
          }
          if (!strcmp($quota, "none")) {
              $quota = 0;
          } else {
              $quota = Pool::formatSize($quota);
          }
          $used = Pool::formatSize($f[2]);
          $usedsnap = Pool::formatSize($f[3]);
          $usedds = Pool::formatSize($f[4]);
          $usedrefres = Pool::formatSize($f[5]);
          $usedchild = Pool::formatSize($f[6]);
          $available = Pool::formatSize($f[1]);
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
          if ($available && $do->available != $available) {
              $upd = true;
              $s->log("updated $do available => $available", LLOG_DEBUG);
              $do->available = $available;
          }
          if ($quota && $do->size != $quota) {
              $upd = true;
              $s->log("updated $do size => $quota", LLOG_DEBUG);
              $do->size = $quota;
          }
          if ($usedchild && $do->uchild != $usedchild) {
              $upd = true;
              $s->log("updated $do uchild => $usedchild", LLOG_DEBUG);
              $do->uchild = $usedchild;
          }
          if ($compressratio && $do->compressratio != $compressratio) {
              $upd = true;
              $s->log("updated $do compressratio => $compressratio", LLOG_DEBUG);
              $do->compressratio = $compressratio;
          }
          if ($origin && $do->origin != $origin) {
              $upd = true;
              $s->log("updated $do origin => $origin", LLOG_DEBUG);
              $do->origin = $origin;
          }
          if ($sd && $do->creation != $sd) {
              $upd = true;
              $s->log("updated $do creation => $sd", LLOG_DEBUG);
              $do->creation = $sd;
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

      return $found_d;
  }

  public static function updateProcess(&$s) {
      // ps -A -o zone,pid,ppid,user,etime,time,tty,args
      return 0;
  }

  public static function updateZfs(&$s)
  {
      $major = $s->data('os:major');
      /* if $major is empty or not a number, it could just be an illumos based machine */
      if (!empty($major) && is_numeric($major) && $major < 10)   {
          return 0;
      }

      $found_z = OSSolaris::getZpoolList($s);
      OSType::cleanRemoved($s, 'a_pool', 'name', $found_z);

      foreach ($s->a_pool as $p) {

          /* update zpool devices */
          $found_v = OSSOlaris::getZpoolDisks($s, $p);
          foreach ($p->a_disk as $d) {
              if (isset($found_v[$d->dev])) {
                  continue;
              }
              $s->log("Removing disk $d from pool $p", LLOG_INFO);
              $p->delFromJT('a_disk', $d);
          }

          /* dataset indexation */
          $found_d = OSSolaris::getZpoolDatasets($s, $p);
          OSType::cleanRemoved($p, 'a_dataset', 'name', $found_d);
      }
  }

  /* Screening */
  public static function htmlDump($s)
  {
      return array(
        'Kernel' => $s->data('os:kernel'),
        'Version' => $s->data('os:major'),
        'Update' => $s->data('os:update'),
       );
  }

    public static function dump($s)
    {
        $ker_ver = $s->data('os:kernel');
        $sol_ver = $s->data('os:major');
        $sol_upd = $s->data('os:update');
        if (empty($ker_ver)) {
            $ker_ver = null;
        }
        if (empty($sol_ver)) {
            $sol_ver = null;
        }
        if (empty($sol_upd)) {
            $sol_upd = null;
        }
        $txt = '';
        $txt .= $s->o_os->name.' ';
        $txt .= ($sol_ver) ? ($sol_ver.' ') : '';
        $txt .= ($sol_upd) ? ('Update '.$sol_upd.' ') : '';
        $txt .= ($ker_ver) ? ('/ Kernel: '.$ker_ver.' ') : '';

        $s->log(sprintf("%15s: %s", 'OS', $txt), LLOG_INFO);
        $s->log(sprintf("%15s: %s", 'Projects', count($s->a_prj).' found'), LLOG_INFO);
    }
}

OSSolaris::$extraActions = array(
  new eAction('Check ZFS Arc', '#', "lAction('actionZFSArc', '%d');", 'id', 'actionZFSArc'),
  new eAction('Check Zone stats', '#', "lAction('actionZoneStats', '%d');", 'id', 'actionZoneStats'),
);
