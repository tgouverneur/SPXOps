<?php
/**
 * SSHSession
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage ssh
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class SSHSession
{

  public $hostname;
    public $port;
    public $o_user = null;

    private $_progress = false;
    private $_stream = null;

    private $_con, $_connected = false;

    public function connect()
    {
        if ($this->_connected) {
            throw new SPXException('Already connected to this server');
        }

        if (!$this->o_user) {
            throw new SPXException('Connection user not specified');
        }

        if (!($this->_con = ssh2_connect($this->hostname, $this->port))) {
            throw new SPXException('Cannot connect');
        }
        $authDone = false;
        if ($this->o_user->pubkey()) {
            if (($rc = ssh2_auth_pubkey_file($this->_con, $this->o_user->username, $this->o_user->pubkey.'.pub', $this->o_user->pubkey))) {
                $authDone = true;
            }
        }
        if (!$authDone && !empty($this->o_user->password)) { /* password auth */
          if (ssh2_auth_password($this->_con, $this->o_user->username, $this->o_user->password)) {
              $authDone = true;
          }
        }
        if ($authDone) {
            $this->_connected = true;
        } else {
            throw new SPXException('Authentication failed');
        }

        return 0;
    }

    public function sendFile($source, $dest, $rights = 0644)
    {
        if (!$this->_connected) {
            throw new SPXException('Cannot sendFile(): not connected');
        }
        return ssh2_scp_send($this->_con, $source, $dest, $rights);
    }

    public function execNB($c)
    { 
        /* exec non blocking */
        if (!$this->_connected) {
            throw new SPXException('Cannot execNB(): not connected');
        }
        $c = $c.";echo \"__COMMAND_FINISHED__\"";
        if ($this->_progress) {
            throw new SPXException('Another command is already in progress.');
        }
        if (!($this->_stream = ssh2_exec($this->_con, $c))) {
            $this->_stream = null;
            throw new SPXException('Cannot get SSH Stream');
        } else {
            stream_set_blocking($this->_stream, false);
            $this->_progress = true;

            return;
        }
    }

    public function stillRunning()
    {
        if ($this->_progress) {
            return true;
        }

        return false;
    }

    public function readFromStream()
    {
        if (!$this->_connected) {
            throw new SPXException('Cannot readFromStrem(): not connected');
        }
        if (!$this->_progress) {
            throw new SPXException('No command running ATM.');
        }
        $buf = '';
        while (1) {
            $wa = null;
            $ex = null;
            $ra = array($this->_stream);
            $nc = stream_select($ra, $wa, $ex, 0, 500000);
            if ($nc) {
                $buf .= stream_get_line($this->_stream, 4096, PHP_EOL).PHP_EOL;
                if (strpos($buf, "__COMMAND_FINISHED__") !== false) {
                    fclose($this->_stream);
                    $this->_progress = false;
                    $buf = str_replace("__COMMAND_FINISHED__\n", "", $buf);

                    return $buf;
                }
            } else {
                return $buf;
            }
        }

        return;
    }

    public function forceClose()
    {
        if (!$this->_connected) {
            throw new SPXException('Cannot forceClose(): not connected');
        }
        if (!$this->_progress) {
            throw new SPXException('No command running ATM.');
        }
        fclose($this->_stream);
        $this->_progress = false;

        return;
    }

    public function execSecure($c, $timeout = 30)
    {
        if (!$this->_connected) {
            throw new SPXException('Cannot execSecure(): not connected');
        }
        $c = $c.";echo \"__COMMAND_FINISHED__\"";
        $time_start = time();
        $buf = "";
        if (!($stream = ssh2_exec($this->_con, $c))) {
            throw new SPXException('Cannot get SSH Stream');
        } else {
            stream_set_blocking($stream, true);
            while (true) {
                $wa = null;
                $ex = null;
                $ra = array($stream);
                $nc = stream_select($ra, $wa, $ex, $timeout);
                if ($nc) {
                    $buf .= stream_get_line($stream, 4096, PHP_EOL).PHP_EOL;
                    if (strpos($buf, "__COMMAND_FINISHED__") !== false) {
                        fclose($stream);
                        $buf = str_replace("__COMMAND_FINISHED__\n", "", $buf);

                        return $buf;
                    }
                    if ((time()-$time_start) > $timeout) {
                        fclose($stream);
                        throw new SPXException('Command timeout');
                    }
                } else {
                    if ((time()-$time_start) >= $timeout) {
                        fclose($stream);
                        throw new SPXException('Command timeout');
                    }
                }
            }
        }
    }

    public function exec($c)
    {
        if (!$this->_connected) {
            throw new SPXException('Cannot exec(): not connected');
        }
        if (!($stream = ssh2_exec($this->_con, $c))) {
            return -1;
        } else {
            stream_set_blocking($stream, true);
            $data = "";
            while ($buf = fread($stream, 4096)) {
                $data .= $buf;
            }
            fclose($stream);

            return $data;
        }
    }

    public function notifyDisconnect($reason, $message, $language)
    {
        $this->_connected = false;
    }

    public function __construct($h = "")
    {
        if (!function_exists("ssh2_connect")) {
            throw new SPXException("SSHSession::__construct: ssh2_connect doesn't exist. please check your ssh2 php installation.");
        }

        $this->hostname = $h;
        $this->port = 22;

        $this->_connected = false;
        $this->_con = null;
    }
}
