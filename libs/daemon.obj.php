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

class Daemon
{
  private $pid = 0;

    public function getPid()
    {
        return $this->pid;
    }

    public function __construct($obj, $f)
    {
        $onull = null;
        if (!defined('SIGHUP')) {
            trigger_error('PHP is compiled without --enable-pcntl directive', E_USER_ERROR);
        }
        pcntl_signal(SIGTERM, array($obj, 'sigterm'));
    //    pcntl_signal(SIGINT,array($obj,'sigterm'));
        pcntl_signal(SIGCHLD, array($obj, 'sigchld'));
        pcntl_signal(SIGHUP, array($obj, 'sighup'));
        pcntl_signal(SIGUSR1, array($obj, 'sigusr1'));
        pcntl_signal(SIGUSR2, array($obj, 'sigusr2'));

        if (!$f) {
            $reco = false;
            if (MySqlCM::getInstance()->isLink()) {
                $reco = true;
            }

            MySqlCM::delInstance();
            gc_collect_cycles();
            $this->pid = pcntl_fork();
            if ($this->pid) {
                $m = MySqlCM::getInstance();
                if ($reco) {
                    $m->connect();
                }

                return;
            } else {
                Logger::delInstance();
                MySqlCM::delInstance();
                pcntl_signal(SIGTERM, array($obj, 'sigterm'));
                pcntl_signal(SIGHUP, array($obj, 'sighup'));
                pcntl_signal(SIGCHLD, array($obj, 'sigchld'));
                pcntl_signal(SIGUSR1, array($obj, 'sigusr1'));
                pcntl_signal(SIGUSR2, array($obj, 'sigusr2'));
                gc_collect_cycles();
                $m = MySqlCM::getInstance();

                $this->pid = posix_getpid();
                $obj->pid = $this->pid;
                $obj->start();
                while (1) {
                    try {
                        if ($obj->run()) {
                            return;
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
            while (1) {
                try {
                    if ($obj->run()) {
                        return;
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
