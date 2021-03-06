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

define('SSH_USTIMEOUT', 200000);
define('SSH_SESSION_RSIZE', 32768);
define('SSH_SFTP_RSIZE', 32768);
define('SSH_EXEC_RETRY', 3);

class SSHSession
{

    public $hostname;
    public $port;
    public $o_user = null;

    private $_con, $_connected = false;

    public function connect() {

        if ($this->_connected) {

            throw new SPXException('Already connected to this server');
        }

        if (!$this->o_user) {

            throw new SPXException('Connection user not specified');
        }

        if (!($this->_con = ssh2_connect($this->hostname, $this->port))) {

            throw new SPXException('Cannot connect to host: '.$this->hostname.':'.$this->port);
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

    private function _reconnect() {

        $this->_connected = false;
        $this->_con = null;
        return $this->connect();
    }

    public function recvFile($source, $dest, $fsize) {

        if (!$this->_connected) {

            throw new SPXException('Cannot recvFile(): not connected');
        }

        if (defined('SSH_DEBUG')) { echo '[D] stat size='.$fsize."\n"; }

        return ssh2_scp_recv($this->_con, $source, $dest);
    }


    public function sendFile($source, $dest, $rights = 0644) {

        if (!$this->_connected) {

            throw new SPXException('Cannot sendFile(): not connected');
        }
        return ssh2_scp_send($this->_con, $source, $dest, $rights);
    }

    public function execSecure($c, $timeout = 30) {

        if (!$this->_connected) {

            throw new SPXException('Cannot execSecure(): not connected');
        }

        $c = $c.';echo "__COMMAND_FINISHED__"';
        $time_start = time();
        $buf = '';
        $stream = null;

        /* Try to run the command up to SSH_EXEC_RETRY in case we fail to get a stream */
        for ($t = 0; $t < SSH_EXEC_RETRY; $t++) {

            if (!($stream = ssh2_exec($this->_con, $c))) {

                if ($this->_reconnect()) {

                    throw new SPXException('Cannot get SSH Stream (reconnection failed)');
                }

                continue;
            }

            break;
        }

        if (!$stream) {

            throw new SPXException('Cannot get SSH Stream');
        } else {

            stream_set_blocking($stream, true);
            stream_set_chunk_size($stream, SSH_SESSION_RSIZE);
            stream_set_write_buffer($stream, 0);
            stream_set_read_buffer($stream, 0);
            stream_set_timeout($stream, 0, SSH_USTIMEOUT);

            while (true) {

                if (defined('SSH_DEBUG')) { echo '[D] Current buf len='.strlen($buf)."\n"; }

                if (strpos($buf, '__COMMAND_FINISHED__') !== false) {

                    fclose($stream);
                    $buf = str_replace("__COMMAND_FINISHED__\n", '', $buf);
                    $buf = str_replace("__COMMAND_FINISHED__\r\n", '', $buf);

                    if (defined('SSH_DEBUG')) { echo '[D] Clean return'."\n"; }

                    return $buf;
                }
 
                while(($chunk = @fread($stream, SSH_SESSION_RSIZE))) {

                    if ($chunk === false) {

                        fclose($stream);
                        throw new SPXException('SSHSession: fread returned false');

                    } else if (strlen($chunk)) {

                        if (defined('SSH_DEBUG')) { echo '[D] SSH Read len='.strlen($chunk)."\n"; }
                        $buf .= $chunk;
                    }

                    $s_info = stream_get_meta_data($stream);

                    /* still something to read */
                    if ($s_info['unread_bytes'] > 0) {

                        continue;
                    }

                    break;
                }
                if ((time() - $time_start) > $timeout) {

                    if (defined('SSH_DEBUG')) { echo '[D] Timeout reached, closing'."\n"; }

                    stream_set_blocking($stream, false);
                    fread($stream, 1);
                    fclose($stream);

                    throw new SPXException('Command Timeout');
                }
            }
        }

    }


    public function exec($c) {

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

    private static function _ssh2_version_ge($ver) {

        if (version_compare(phpversion('ssh2'), $ver) >= 0) {

            return true;
        }

        return false;
    }

    public function disconnect() {

        if ($this->_connected && SSHSession::_ssh2_version_ge('1.0')) {

            ssh2_disconnect($this->_con);
        }
    }

    public function notifyDisconnect($reason, $message, $language) {

        $this->_connected = false;
    }

    public function __destruct() {

        $this->disconnect();
    }

    public function __construct($h = "") {

        if (!function_exists("ssh2_connect")) {
            throw new SPXException("SSHSession::__construct: ssh2_connect doesn't exist. please check your ssh2 php installation.");
        }

        $this->hostname = $h;
        $this->port = 22;

        $this->_connected = false;
        $this->_con = null;
    }
}
