<?php
/**
 * SPXMsg object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2014, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
define('MAX_MSG_SIZE', 65000);

 class SPXMsg
 {
   private $_bs = null; /* Byte stream */
   private $_ebs = null; /* Encrypted byte stream */
   private $_key = null;
     private $_iv = null;
     private $_ivsize = null;
     private $_network = null;

     public $a_v = array();

     public $from = '';
     public $len = '';
     public $port = 0;

     public function __construct($net = null)
     {
         $this->_key = pack('H*', Config::$server_key);
         if ($net) {
             $this->_network = $net;
             $this->_ivsize = $this->_network->ivsize;
         } else {
             $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
             $this->_iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
         }
     }

     private function _unSerialize()
     {
         $this->a_v = unserialize($this->_bs);
         Logger::log("[-] Unserialized: $this", $this, LOG_DEBUG);

         return 0;
     }

     private function _serialize()
     {
         $this->_bs = serialize($this->a_v);

         return 0;
     }

     private function _enCrypt()
     {
         if ($this->_bs) {
             $this->_ebs = base64_encode($this->_iv.mcrypt_enCrypt(MCRYPT_RIJNDAEL_128, $this->_key, $this->_bs, MCRYPT_MODE_CBC, $this->_iv));

             return 0;
         }

         return 1;
     }

     private function _deCrypt()
     {
         if ($this->_ebs) {
             $tmp = base64_decode($this->_ebs);
             $this->_iv = substr($tmp, 0, $this->_ivsize);
             $ct = substr($tmp, $this->_ivsize);
             $this->_bs = mcrypt_deCrypt(MCRYPT_RIJNDAEL_128, $this->_key, $ct, MCRYPT_MODE_CBC, $this->_iv);
             Logger::log("[-] Decrypted: $this", $this, LOG_DEBUG);
         }

         return 1;
     }

     public function recv($sock)
     {
         $this->ebs = '';
         $this->len = socket_recvfrom($sock, $this->_ebs, MAX_MSG_SIZE, 0, $this->from, $this->port);
         Logger::log("Received: $this", $this);
         $this->_deCrypt();
         $this->_unSerialize();
         Logger::log("Received ".$this->len." bytes from ".$this->from.":".$this->port, $this);
     }

     public function send($sock, $to, $port)
     {
         $this->a_v['hostname'] = Config::$agentname;
         $this->_serialize();
         $this->_enCrypt();
         $len = socket_sendto($sock, $this->_ebs, strlen($this->_ebs), 0, $to, $port);
         echo "[-] Sent $len bytes to $to:$port\n";

         return $len;
     }

     public function __toString()
     {
         return $this->from.':'.$this->port.'('.$this->len.')';
     }
 }
