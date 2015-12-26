<?php
trait sshTrait {

  private $_ssh = null;
  private $_paths = array();

  public function connect()
  {
      try {
          if (!$this->o_suser && $this->fk_suser > 0) {
              $this->_fetchFK('fk_suser');
          }

          $this->_ssh = new SSHSession($this->hostname);
          if ($this->o_suser) {
              $this->_ssh->o_user = $this->o_suser;
              $this->_ssh->connect();
          } else {
              /* try to detect sUser */
              $a_suser = SUSer::getAll();
              foreach($a_suser as $suser) {
                  $this->o_suser = $suser;
                  try {
                      $this->_ssh->o_user = $suser;
                      $this->_ssh->connect();
                      $this->fk_suser = $suser->id;
                      $this->update();
                  } catch (Exception $e) {
                      continue;
                  }
              }
          }

          return 0;
      } catch (Exception $e) {
          throw $e;
      }
  }

  public function recvFile($source, $dest) {
      $fsize = $this->fileStat($source);
      return $this->_ssh->recvFile($source, $dest, $fsize);
  }

  public function disconnect()
  {
      $this->_ssh = null;
  }

  public function execNB($cmd, $args = null, $timeout = 30)
  {
      $v_cmd = '';

      if ($args) {
          $v_cmd = vsprintf($cmd, $args);
      } else {
          $v_cmd = $cmd;
      }
      try {
          $this->_ssh->execNB($v_cmd, $timeout);
      } catch (Exception $e) {
          throw $e;
      }

      return;
  }

  public function stillRunning()
  {
      return $this->_ssh->stillRunning();
  }

  public function readFromStream()
  {
      try {
          return $this->_ssh->readFromStream();
      } catch (Exception $e) {
          return false;
      }
  }

  public function isConnected() {
      if ($this->_ssh) {
          return true;
      }
      return false;
  }

  public function forceCloseExec()
  {
      try {
          $this->_ssh->forceClose();
          return true;
      } catch (Exception $e) {
          return false;
      }
  }

  public function exec($cmd, $args = null, $timeout = 120)
  {
      //Logger::log('CMD_RUN: '.$cmd, $this, LLOG_INFO);
      $v_cmd = '';
      if ($args) {
          $v_cmd = vsprintf($cmd, $args);
      } else {
          $v_cmd = $cmd;
      }
      try {
          $buf = $this->_ssh->execSecure($v_cmd, $timeout);
      } catch (Exception $e) {
          throw $e;
      }

      return trim($buf);
  }

  public function isFile($path)
  {
      if (!$this->_ssh) {
          throw new SPXException('SSH Not connected');
      }

      if (empty($path)) {
          throw new SPXException('Path not provided');
      }

      try {
          $r = $this->_ssh->execSecure('test -f '.$path.' && echo 1', 10);
      } catch (Exception $e) {
          throw($e);
      }
      if (!empty($r)) {
          if ($r == 1) {
              return true;
          }
      }
      return false;
  }

  public function fileStat($file) {
      $stat = $this->findBin('stat');
      $ret = $this->exec($stat.' -c%s '.$file);
      return $ret;
  }

  public function findBin($bin, $paths = null)
  {
      if (!$this->_ssh) {
          throw new SPXException('SSH Not connected');
      }

      if (isset($this->_paths[$bin])) {
          return $this->_paths[$bin];
      }

    /* add the default array of path into $paths or load the one from the OS specific class */
    if (!$paths) {
      if (!$this->fk_os || $this->fk_os == -1) {
          $paths = OS::$binPaths;
      } else {
          if (!$this->o_os) {
              $this->fetchFK('fk_os');
          }
          $oclass = $this->o_os->class;
          $paths = $oclass::$binPaths;
      }
    }

    foreach ($paths as $path) {
        $bpath = $path.'/'.$bin;
        try {
            $r = $this->_ssh->execSecure('test -x '.$bpath.' && echo 1', 10);
        } catch (Exception $e) {
            throw($e);
        }
        if (!empty($r)) {
            if ($r == 1) {
              /* store it for later use */
              $this->_paths[$bin] = $bpath;

              return $bpath;
            }
        }
    }
    throw new SPXException($bin.' not found on '.$this);
  }

}
?>
