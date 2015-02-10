<?php
/**
 * SPXNet object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2014, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */
define('SPXQ_ID', 0x4221);

class SPXNet
{
  public $pid = -1;
    public $ppid = -1;

    private $_sock = null;
    private $_port = null;
    private $_host = null;
    private $lrun = 0;
    private $crun = 0;
    private $_foreground = false;

    public $ivsize = -1;

  /* pool for msg processing */
  private $_nrProcess = 5; // use Setting:: for this
  private $curProcess = 0;
    private $f_master = 0;
    private $_nqueue = null;

    private $a_pid = array();

    public function log($str, $lvl = LLOG_INFO)
    {
        $obj = null;
        Logger::log($str, $obj, $lvl);
    }

    public function setForeground($f = false)
    {
        $this->_foreground = $f;
    }

    public function cleanup()
    {
        socket_close($this->_sock);
        if ($this->_foreground) {
            Logger::closeLog();
        }
        if ($this->f_master && $this->_nqueue) {
            $this->_nqueue->destroy();
        }
    }

    public function sigterm()
    {
        $this->cleanup();
        die();
    }

    public function sighup()
    {
        if (!$this->_foreground) {
            Logger::openLog();
        }
        if (!$this->_foreground) {
            Logger::closeLog();
        }
    }

    public function sigchld()
    {
        while (true) {
            $p = pcntl_waitpid(-1, $status, WNOHANG);
            if ($p > 0) {
                $this->log("We just reaped a child: $p", LLOG_WARN);
            } else {
                return;
            }
        }
    }

    public function sigkill()
    {
        $this->cleanup();
        die();
    }

    public function sigusr1()
    {
    }

    public function sigusr2()
    {
    }

    public function spawnProcess()
    {
        global $config;
        $this->network = new SPXNet(null, null, $this, 0);
        $daemon = new Daemon($this->network, false);
        $m = MysqlCM::getInstance();
        $m->connect();
        $pid = new Pid();
        $pid->agent = $config['agentname'];
        $pid->pid = $daemon->getPid();
        $pid->ppid = $this->pid;
        $pid->f_master = 0;

        return $pid;
    }

    public function start()
    {
        global $config;

        if ($this->f_master) {
            $config['spxopsd']['log'] .= '-net_'.$this->pid;
        } else {
            $config['spxopsd']['log'] .= '-'.$this->pid; // slave network log
        }
        if (!$this->_foreground) {
            Logger::openLog();
        }
        Logger::log("Starting Network server on ".$this->_host.":".$this->_port, $this, LLOG_DEBUG);

        if ($this->f_master) {
            $this->_sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($this->_sock === false) {
                $err = socket_last_error();
                $estr = socket_strerror($err);
                throw new SPXException('socket_create(): '.$estr);
            }
            $rc = socket_bind($this->_sock, $this->_host, $this->_port);
            if ($rc === false) {
                $err = socket_last_error();
                $estr = socket_strerror($err);
                throw new SPXException('socket_bind(): '.$estr);
            }
            $this->ivsize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

      // get the queue, remove it
      $this->_nqueue = new SPXQueue(SPXQ_ID);
            $this->_nqueue->destroy();
        }
        $this->_nqueue = new SPXQueue(SPXQ_ID);
    }

    public function run()
    {
        try {
            $m = mysqlCM::getInstance();
            if ($m->connect()) {
                $this->log(" Error with SQL db: ".$m->getError());
                sleep($this->_interval);

                return -1;
            }
        } catch (Exception $e) {
            sleep($this->_interval);
            throw($e);
        }

        if ($this->f_master) {
            /* every 10s, check for dead child and spawn new ones eventually */
      $this->_crun = time();
            if ($this->_crun - $this->lrun > 10) {
                $this->_lrun = $this->_crun;
        /* reap eventual dead childs */
        $this->sigchld();
                foreach ($this->a_pid as $p => $po) {
                    $po->checkMe($this);
                    if ($po->f_dead) {
                        Logger::log("SNetwork pid $po has died", $this, LLOG_DEBUG);
                        $this->curProcess--;
                        $this->a_pid[$p] = null;
                        unset($this->a_pid[$p]);
                    }
                }
                while ($this->curProcess < $this->_nrProcess) {
                    $pid = $this->spawnProcess();
                    $this->log("Spawned other process: $pid", LLOG_DEBUG);
                    $this->a_pid[$pid->pid] = $pid;
                    $this->curProcess++;
                }
            }

            Logger::log('SPXNet::run()', $this, LLOG_DEBUG);
            $msg = new SPXMsg($this);
            Logger::log('recv() start', $this, LLOG_DEBUG);
            $msg->recv($this->_sock);
            Logger::log('recv() done', $this, LLOG_DEBUG);
            $msg->a_v['from'] = $msg->from;
            $msg->a_v['port'] = $msg->port;
            if (!$this->_nqueue->send($msg->a_v)) {
                Logger::log('Error sending message to queue!');

                return;
            }
            Logger::log("stats: ".print_r($this->_nqueue->stat(), true), $this, LLOG_DEBUG);
        } else { // child, get message and use them, loop forever to avoid connect/disconnect of mysql
      try {
          $msg = new SPXMsg();
          if (!$this->_nqueue->receive($msg->a_v)) {
              Logger::log('Error receiving message from queue!');

              return;
          }
          $msg->from = $msg->a_v['from'];
          $msg->port = $msg->a_v['port'];
          Logger::log('useMSG() start', $this, LLOG_DEBUG);
          $this->useMSG($msg);
          Logger::log('useMSG() end', $this, LLOG_DEBUG);
      } catch (Exception $e) {
          Logger::log('Exception found in net_child: '.$e, $this, LLOG_DEBUG);
      }
        }

        $m->disconnect();
    }

    public function useMSG($msg)
    {
        if ($msg->a_v === false) {
            Logger::log("$msg: invalid", $this, LLOG_DEBUG);

            return 1;
        }

        if (!isset($msg->a_v['hostname']) || empty($msg->a_v['hostname'])) {
            Logger::log("$msg: no hostname specified", $this, LLOG_DEBUG);

            return 1;
        }

    /* check if we find an RRD for this */
    if (!isset($msg->a_v['type']) || empty($msg->a_v['type'])) {
        Logger::log("$msg: no type specified", $this, LLOG_DEBUG);

        return 2;
    }

        $s = new Server();
    /* @TODO: Sanitize input from packet, even if encrypted... */
    $s->hostname = $msg->a_v['hostname'];
        if ($s->fetchFromField('hostname')) {
            Logger::log("$msg: Hostname specified not found in database", $this, LLOG_DEBUG);

            return 2;
        }
        $s->fetchRL('a_rrd');
        try {
            RRD::parseData($s, $msg->a_v);
        } catch (Exception $e) {
            Logger::log("$msg: $e", $this, LLOG_DEBUG);

            return 3;
        }
    }

    public function __construct($host, $port, $parent = null, $master = 0)
    {
        if ($parent) {
            $this->_ppid = $parent->pid;
            $this->setForeground(false);
        }
        $this->f_master = $master;
        $this->_port = $port;
        $this->_host = $host;
    }
}
