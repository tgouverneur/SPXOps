<?php
 /**
  * Daemon object
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage device
  * @category classes
  * @filesource
  */


interface Daemonizable {

  public function run();
  public function start();
  public function cleanup();
  public function sigterm();
  public function sighup();
  public function sigchld();
  public function sigkill();
  public function sigusr1();
  public function sigusr2();
}

class Daemon
{
  private $pid = 0;

  public function getPid() {
    return $this->pid;
  }
  
  public function __construct($obj, $f) 
  { 
    $onull = null;
    if (!defined('SIGHUP')){
        trigger_error('PHP is compiled without --enable-pcntl directive', E_USER_ERROR);
    }
    pcntl_signal(SIGTERM,array($obj,'sigterm'));
//    pcntl_signal(SIGINT,array($obj,'sigterm'));
    pcntl_signal(SIGCHLD,array($obj,'sigchld'));
    pcntl_signal(SIGHUP,array($obj,'sighup'));
    pcntl_signal(SIGUSR1,array($obj,'sigusr1')); 
    pcntl_signal(SIGUSR2,array($obj,'sigusr2')); 
 
    if (!$f) {

      mysqlCM::delInstance();
      $this->pid = pcntl_fork();
      if ($this->pid) {
        echo "Forked\n";
        return;
      } else {
        Logger::delInstance();
        pcntl_signal(SIGTERM,array($obj,'sigterm'));
        pcntl_signal(SIGHUP,array($obj,'sighup'));
        pcntl_signal(SIGCHLD,array($obj,'sigchld'));
        pcntl_signal(SIGUSR1,array($obj,'sigusr1')); 
        pcntl_signal(SIGUSR2,array($obj,'sigusr2')); 
        $m = mysqlCM::getInstance();
 
        $this->pid = posix_getpid();
        $obj->pid = $this->pid;
        $obj->start();
        while(1) {
          try {
            if ($obj->run()) {
              exit(0);
            }
	  } catch (Exception $e) {
	    Logger::log("Exception catched in run(): $e", $onull, LLOG_ERR);
	    $m = MysqlCM::getInstance();
	    $m->disconnect();
	    continue;
	  }
        }
      }
    } else {
      $this->pid = posix_getpid();
      $obj->pid = $this->pid;
      $obj->start();
      while(1) {
        try {  
          if ($obj->run()) {
	    exit(0);
	  }
        } catch (Exception $e) {
          Logger::log("Exception catched in run(): $e", $onull, LLOG_ERR);
          $m = MysqlCM::getInstance();
          $m->disconnect();
          continue;
        }
      }
    }
  }
}

?>
