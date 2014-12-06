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

class SPXNet {
  public $pid = -1;
  public $ppid = -1;

  private $_sock = null;
  private $_port = null;
  private $_host = null;
  private $_foreground = false;

  public $ivsize = -1;

  public function setForeground($f = false) {
    $this->_foreground = $f;
  }

  public function cleanup() {
    socket_close($this->_sock);
    if ($this->_foreground) Logger::closeLog();
  }

  public function sigterm() {
    $this->cleanup();
    die();
  }

  public function sighup() {
    if (!$this->_foreground) Logger::openLog();
    if (!$this->_foreground) Logger::closeLog();
  }

  public function sigchld() {
    while(true) {
       $p = pcntl_waitpid(-1, $status, WNOHANG);
       if($p > 0){
         $this->log("We just reaped a child: $p", LLOG_WARN);
       } else {
         return;
       }
    }
  }

  public function sigkill() {
    $this->cleanup();
    die();
  }

  public function sigusr1() {
  }

  public function sigusr2() {
  }

  public function start() {
    global $config;

    $config['spxopsd']['log'] = $config['spxopsd']['log'].'-net_'.$this->pid;
    if (!$this->_foreground) Logger::openLog();
    Logger::log("Starting Network server on ".$this->_host.":".$this->_port, $this, LLOG_DEBUG);

    $this->_sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_bind($this->_sock, $this->_host, $this->_port);

    $this->ivsize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
  }

  public function run() {
    Logger::log('SPXNet::run()', $this, LLOG_DEBUG);
    $msg = new SPXMsg($this);
    $msg->recv($this->_sock);
  }

  public function __construct($host, $port, $parent = null) {
    if ($parent) {
      $this->_ppid = $parent->pid;
      $this->setForeground(false);
    }
    $this->_port = $port;
    $this->_host = $host;
  }
}


?>
