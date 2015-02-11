<?php
/**
 * RRD object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2014, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */
class RRD extends MySqlObj
{
  public static $RIGHT = 'SRV';

    public $id = -1;
    public $path = '';
    public $type = '';
    public $name = '';
    public $f_lock = 0;
    public $fk_disk = -1;
    public $fk_pool = -1;
    public $fk_server = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;
    public $o_pool = null;
    public $o_disk = null;

    private $_log = null;

    public function checklock()
    {
        $lf = $this->getPath().'.lock';
        if (file_exists($lf)) {
            $pid = getmypid();
            $fp = file_get_contents($lf, ''.getmypid());
            if ($pid != $fp) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function lock()
    {
        $lf = $this->getPath().'.lock';
        if (file_exists($lf)) {
            throw new SPXException($this->path.' is already locked');
        }
        file_put_contents($lf, ''.getmypid());

        return;
    }

    public function unlock()
    {
        $lf = $this->getPath().'.lock';
        if (file_exists($lf)) {
            $pid = getmypid();
            $lc = file_get_contents($lf);
            Logger::log("compare: $lc vs $pid", $this, LOG_DEBUG);
            if ($lc == $pid) {
                Logger::log("unlink: $lf", $this, LOG_DEBUG);
                $rc = unlink($lf);
                Logger::log("unlink: done $rc", $this, LOG_DEBUG);
            } else {
                throw new SPXException('We are not the owner for the lock of '.$this->path);
            }
        } else {
            return true;
        }

        return;
    }

  /* @TODO: dafuq, work on better argument name :-) */
  public function getData($start, $what, $n)
  {
      $what = $this->getWhat($what);
      if (!strcmp($start, 'NOW')) {
          $start = '-'.$n.'s';
          $end = 'start+'.$n.'s';
      } else {
          $end = $start; // haha, yeah I know...
      $start -= $n;
      }
      $result = rrd_fetch($this->getPath(), array( 'AVERAGE', '--start', $start, '--end', $end ));
    /* first filter result */
    $ret = array();
      $ret['values'] = array();
      $ret['labels'] = array();
      $i = 0;
      foreach ($what as $k => $d) {
          $ret['labels'][$i] = array('label' => $what[$k]);
          $ret['values'][$i] = array();
          foreach ($result['data'][$k] as $t => $v) {
              $ret['values'][$i][] = array($t, $v);
          }
          $i++;
      }

      return $ret;
  }

    public function getPath()
    {
        return Config::$server_rrdpath.'/'.$this->path;
    }

    public function getWhat($what)
    {
        switch ($this->type) {
      case 'mpstat':
        if (!strcmp($what, 'default')) {
            $what = 'usr,sys,idl';
        }
        $all = array(
                        'minf' => 'Minor faults',
                        'mjf' => 'Major faults',
                        'xcal' => 'IPC Calls',
                        'intr' => 'Interrupts',
                        'ithr' => 'Interrupts as threads',
                        'csw' => 'Context switches',
                        'icsw' => 'Involuntary CSW',
                        'migr' => 'Threads migrations',
                        'smtx' => 'Spins on mutexes',
                        'srw' => 'Spins on r/w locks',
                        'syscl' => 'Syscalls',
                        'usr' => 'User time',
                        'sys' => 'System time',
                        'st' => 'st',
                        'idl' => 'Idle time',

        );
        if (!strcmp($what, 'all')) {
            return $all;
        }
        $f = explode(',', $what);
        $ret = array();
        foreach ($f as $v) {
            if (empty($v)) {
                continue;
            }
            if (isset($all[$v])) {
                $ret[$v] = $all[$v];
            }
        }

        return $ret;
      break;
      case 'ziostat':
        if (!strcmp($what, 'default')) {
            $what = 'rb,wb';
        }
        $all = array(
                        'rops' => 'Read IOPS',
                        'wops' => 'Write IOPS',
                        'rb' => 'Bytes read per second',
                        'wb' => 'Bytes writen per second',

        );
        if (!strcmp($what, 'all')) {
            return $all;
        }
        $f = explode(',', $what);
        $ret = array();
        foreach ($f as $v) {
            if (empty($v)) {
                continue;
            }
            if (isset($all[$v])) {
                $ret[$v] = $all[$v];
            }
        }

        return $ret;
      break;
      case 'iostat':
        if (!strcmp($what, 'default')) {
            $what = 'krs,kws,asvc,bpc';
        }
    $all = array(
                    'riops' => 'Read IOPS',
                        'wiops' => 'Write IOPS',
                        'wait' => 'Wait',
                        'wsvc' => 'wsvc',
                        'wpc' => 'wpc',
            'krs' => 'KB read per second',
            'kws' => 'KB writen per second',
            'asvc' => 'Average Service time',
            'bpc' => '% Busy',

    );
        if (!strcmp($what, 'all')) {
            return $all;
        }
        $f = explode(',', $what);
        $ret = array();
        foreach ($f as $v) {
            if (isset($all[$v])) {
                $ret[$v] = $all[$v];
            }
        }

        return $ret;
      break;
      default:
        return array();
      break;
    }
    }

    public function rupdate($ts, $a)
    {
        $updator = new RRDUpdater($this->getPath());

        return $updator->update($a, $ts);
    }

    private function createMpStat()
    {
        $creator = new RRDCreator($this->getPath(), "now -10d", 1);
        $creator->addDataSource('minf:GAUGE:1:0:U');
        $creator->addDataSource('mjf:GAUGE:1:0:U');
        $creator->addDataSource('xcal:GAUGE:1:0:U');
        $creator->addDataSource('intr:GAUGE:1:0:U');
        $creator->addDataSource('ithr:GAUGE:1:0:U');
        $creator->addDataSource('csw:GAUGE:1:0:U');
        $creator->addDataSource('icsw:GAUGE:1:0:U');
        $creator->addDataSource('migr:GAUGE:1:0:U');
        $creator->addDataSource('smtx:GAUGE:1:0:U');
        $creator->addDataSource('srw:GAUGE:1:0:U');
        $creator->addDataSource('syscl:GAUGE:1:0:U');
        $creator->addDataSource('usr:GAUGE:1:0:U');
        $creator->addDataSource('sys:GAUGE:1:0:U');
        $creator->addDataSource('st:GAUGE:1:0:U');
        $creator->addDataSource('idl:GAUGE:1:0:U');
        $creator->addArchive('AVERAGE:0:1:604800');
        $creator->addArchive('AVERAGE:0.5:60:44640');
        $creator->addArchive('AVERAGE:0.5:300:105120');
        $creator->save();
        Logger::log('RRD '.$this->path.' created with MPSTAT type', $this, LOG_DEBUG);
    }

    private function createZIOStat()
    {
        $creator = new RRDCreator($this->getPath(), "now -10d", 1);
        $creator->addDataSource('rops:GAUGE:1:0:U');
        $creator->addDataSource('wops:GAUGE:1:0:U');
        $creator->addDataSource('rb:GAUGE:1:0:U');
        $creator->addDataSource('wb:GAUGE:1:0:U');
        $creator->addArchive('AVERAGE:0:1:604800');
        $creator->addArchive('AVERAGE:0.5:60:44640');
        $creator->addArchive('AVERAGE:0.5:300:105120');
        $creator->save();
        Logger::log('RRD '.$this->path.' created with ZIOSTAT type', $this, LOG_DEBUG);
    }

    private function createIOStat()
    {
        $creator = new RRDCreator($this->getPath(), "now -10d", 1);
        $creator->addDataSource('riops:GAUGE:1:0:U');
        $creator->addDataSource('wiops:GAUGE:1:0:U');
        $creator->addDataSource('krs:GAUGE:1:0:U');
        $creator->addDataSource('kws:GAUGE:1:0:U');
        $creator->addDataSource('wait:GAUGE:1:0:U');
        $creator->addDataSource('actv:GAUGE:1:0:U');
        $creator->addDataSource('wsvc:GAUGE:1:0:U');
        $creator->addDataSource('asvc:GAUGE:1:0:U');
        $creator->addDataSource('wpc:GAUGE:1:0:100');
        $creator->addDataSource('bpc:GAUGE:1:0:100');
        $creator->addArchive('AVERAGE:0:1:604800');
        $creator->addArchive('AVERAGE:0.5:60:44640');
        $creator->addArchive('AVERAGE:0.5:300:105120');
        $creator->save();
        Logger::log('RRD '.$this->path.' created with IOSTAT type', $this, LOG_DEBUG);
    }

    public function create()
    {
        if (!isset(Config::$server_rrdpath) || empty(Config::$server_rrdpath)) {
            throw new SPXException('RRD Path not set!');
        }

        switch ($this->type) {
          case 'mpstat':
             $this->createMpStat();
             break;
          case 'ziostat':
             $this->createZIOStat();
             break;
          case 'iostat':
             $this->createIOStat();
             break;
          default:
             throw new SPXException('RRD::create(): Unknown type');
          break;
    }

        return 0;
    }

    public static function parseMPstat(&$s, $a)
    {
        if (!isset($a['values']) || !count($a['values'])) {
            throw new SPXException('No values provided');
        }

        if (!isset($a['ts']) || !is_numeric($a['ts'])) {
            throw new SPXException('No correct TS provided');
        }
        $ts = $a['ts'];
        unset($a['values']['sze']);

        try {
            /* find RRD by path */
      $path = $s->hostname.'-mpstat.rrd';
            $rrd = new RRD();
            $rrd->path = $path;
            if ($rrd->fetchFromField('path')) {
                Logger::log("lock() $rrd", $this, LOG_DEBUG);
                $rrd->lock();
                $rrd->path = $path;
                $rrd->type = 'mpstat';
                $rrd->name = '';
                $rrd->fk_server = $s->id;
                if (!$rrd->checklock()) {
                    throw new SPXException($path.': lock was not acquired properly');
                }
                $rrd->insert();
                if (!$rrd->checklock()) {
                    $rrd->delete();
                    throw new SPXException($path.': lock was not acquired properly');
                }
                $rrd->create();
                Logger::log("create() $rrd", $this, LOG_DEBUG);
                if (!$rrd->checklock()) {
                    $rrd->delete();
                    throw new SPXException($path.': lock was not acquired properly');
                }
                $rrd->unlock();
                Logger::log("unlock() $rrd", $this, LOG_DEBUG);
            }
            $rc = $rrd->rupdate($ts, $a['values']);
            if ($rc) {
                Logger::log("Updated $rrd", $this, LOG_DEBUG);
            } else {
                Logger::log("Failure while updating $rrd", $this, LOG_DEBUG);
            }
        } catch (Exception $e) {
            Logger::log("Failed using data for $rrd: $e", $this, LOG_DEBUG);
        }
    }

    public static function parseZIostat(&$s, $a)
    {
        if (!isset($a['values']) || !count($a['values'])) {
            throw new SPXException('No values provided');
        }

        if (!isset($a['ts']) || !is_numeric($a['ts'])) {
            throw new SPXException('No correct TS provided');
        }
        $ts = $a['ts'];

        foreach ($a['values'] as $pool => $values) {
            try {
                /* find RRD by path */
        $path = $s->hostname.'-ziostat-'.$pool.'.rrd';
                $rrd = new RRD();
                $rrd->path = $path;
                if ($rrd->fetchFromField('path')) {
                    Logger::log("lock() $rrd", $this, LOG_DEBUG);
                    $rrd->lock();
                    $rrd->path = $path;
                    $rrd->type = 'ziostat';
                    $rrd->name = $pool;
                    $rrd->fk_server = $s->id;
      /* @TODO: link pool */
          if (!$rrd->checklock()) {
              throw new SPXException($path.': lock was not acquired properly');
          }
                    $rrd->insert();
                    if (!$rrd->checklock()) {
                        $rrd->delete();
                        throw new SPXException($path.': lock was not acquired properly');
                    }
                    $rrd->create();
                    Logger::log("create() $rrd", $this, LOG_DEBUG);
                    if (!$rrd->checklock()) {
                        $rrd->delete();
                        throw new SPXException($path.': lock was not acquired properly');
                    }
                    $rrd->unlock();
                    Logger::log("unlock() $rrd", $this, LOG_DEBUG);
                }
                $rc = $rrd->rupdate($ts, $values);
                if ($rc) {
                    Logger::log("Updated $rrd", $this, LOG_DEBUG);
                } else {
                    Logger::log("Failure while updating $rrd", $this, LOG_DEBUG);
                }
            } catch (Exception $e) {
                Logger::log("Failed using data for $rrd: $e", $this, LOG_DEBUG);
            }
        }
    }

    public static function parseIostat(&$s, $a)
    {
        if (!isset($a['values']) || !count($a['values'])) {
            throw new SPXException('No values provided');
        }

        if (!isset($a['ts']) || !is_numeric($a['ts'])) {
            throw new SPXException('No correct TS provided');
        }
        $ts = $a['ts'];

        foreach ($a['values'] as $disk => $values) {
            try {
                /* find RRD by path */
                $path = $s->hostname.'-iostat-'.$disk.'.rrd';
                $rrd = new RRD();
                $rrd->path = $path;
                if ($rrd->fetchFromField('path')) {
                    $rrd->lock();
                    Logger::log("lock() $rrd", $this, LOG_DEBUG);
                    $rrd->path = $path;
                    $rrd->type = 'iostat';
                    $rrd->name = $disk;
                    $rrd->fk_server = $s->id;
      /* @TODO: link pool */
      /* @TODO: link disk */
          $rrd->insert();
                    $rrd->create();
                    Logger::log("create() $rrd", $this, LOG_DEBUG);
                    $rrd->unlock();
                    Logger::log("unlock() $rrd", $this, LOG_DEBUG);
                }

                $rc = $rrd->rupdate($ts, $values);
                if ($rc) {
                    Logger::log("Updated $rrd", $this, LOG_DEBUG);
                } else {
                    Logger::log("Failure while updating $rrd", $this, LOG_DEBUG);
                }
            } catch (Exception $e) {
                Logger::log("Failed using data for $rrd: $e", $this, LOG_DEBUG);
            }
        }
    }

    public static function parseData(&$s, $a)
    {
        if (!isset($a['type']) || empty($a['type'])) {
            throw new SPXException('no type specified');
        }
        switch ($a['type']) {
      case 'mpstat':
        Logger::log('entering parseMPstat()', $this, LLOG_DEBUG);
        return RRD::parseMPstat($s, $a);
      break;
      case 'ziostat':
        Logger::log('entering parseZIostat()', $this, LLOG_DEBUG);
        return RRD::parseZIostat($s, $a);
      break;
      case 'iostat':
        Logger::log('entering parseIostat()', $this, LLOG_DEBUG);
        return RRD::parseIostat($s, $a);
      break;
      default:
        throw new SPXException('Unknown type');
      break;
    }
    }

    public function equals($z)
    {
        if (!strcmp($this->path, $z->path)) {
            return true;
        }

        return false;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_server && $this->fk_server > 0) {
                $this->fetchFK('fk_server');
            }

            if (!$this->o_pool && $this->fk_pool > 0) {
                $this->fetchFK('fk_pool');
            }

            if (!$this->o_disk && $this->fk_disk > 0) {
                $this->fetchFK('fk_disk');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function link()
    {
        return '<a href="/view/w/rrd/i/'.$this->id.'">'.$this.'</a>';
    }

    public function __toString()
    {
        return $this->type.'/'.$this->name;
    }

    public function dump($s)
    {
        $s->log(sprintf("\t%15s - %s", $this->type, $this->path), LLOG_INFO);
    }

    public static function printCols($cfs = array())
    {
        return array('Type' => 'type',
                 'Path' => 'path',
                 'Server' => 'server',
                 'Pool' => 'pool',
                 'Disk' => 'disk',
                );
    }

    public function toArray($cfs = array())
    {
        if (!$this->o_server && $this->fk_server > 0) {
            $this->fetchFK('fk_server');
        }

        if (!$this->o_pool && $this->fk_pool > 0) {
            $this->fetchFK('fk_pool');
        }

        if (!$this->o_disk && $this->fk_disk > 0) {
            $this->fetchFK('fk_disk');
        }

        return array(
                 'type' => $this->type,
                 'path' => $this->path,
                 'server' => ($this->o_server) ? $this->o_server->hostname : 'Unknown',
                 'pool' => ($this->o_pool) ? $this->o_pool->name : 'Unknown',
                 'disk' => ($this->o_disk) ? $this->o_disk->dev : 'Unknown',
                );
    }

    public function htmlDump()
    {
        if (!$this->o_server && $this->fk_server > 0) {
            $this->fetchFK('fk_server');
        }

        if (!$this->o_pool && $this->fk_pool > 0) {
            $this->fetchFK('fk_pool');
        }

        if (!$this->o_disk && $this->fk_disk > 0) {
            $this->fetchFK('fk_disk');
        }

        @include_once Config::$rootpath.'/libs/functions.lib.php';

        $ret = array(
    'Type' => $this->type,
    'Path' => $this->path,
    'Server' => ($this->o_server) ? $this->o_server->link() : 'Unknown',
    'Pool' => ($this->o_pool) ? $this->o_pool->link() : 'Unknown',
    'Disk' => ($this->o_disk) ? $this->o_disk->dev : 'Unknown',
    'Updated on' => date('d-m-Y', $this->t_upd),
    'Added on' => date('d-m-Y', $this->t_add),
    );

        return $ret;
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_rrd';
    //$this->_nfotable = 'nfo_rrd';
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'path' => SQL_PROPE|SQL_EXIST,
                        'type' => SQL_PROPE,
                        'name' => SQL_PROPE,
                        'f_lock' => SQL_PROPE,
                        'fk_pool' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        'fk_disk' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'path' => 'path',
                        'type' => 'type',
                        'name' => 'name',
                        'f_lock' => 'f_lock',
                        'fk_pool' => 'fk_pool',
                        'fk_server' => 'fk_server',
                        'fk_disk' => 'fk_disk',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");
      $this->_addFK("fk_pool", "o_pool", "Pool");
      $this->_addFK("fk_disk", "o_disk", "Disk");

      $this->_log = Logger::getInstance();
  }
}
