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
 */

define('MAX_MSG_SIZE', 1000);

 class SPXMsg {
   private $_bs = null; /* Byte stream */
   private $_ebs = null; /* Encrypted byte stream */
   private $_key = null;
   private $_iv = null;
   private $_ivsize = null;
   private $_network = null;

   public $a_v = array();

   public $from = '';
   public $port = 0;

   public function __construct($net = null) {
     global $config;
     $this->_key = pack('H*', $config['server']['key']);
     if ($net) {
       $this->_network = $net;
       $this->_ivsize = $this->_network->ivsize;
     } else {
       $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
       $this->_iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
       $this->a_v['hostname'] = $config['agentname'];
     }
   }

   private function _unserialize() {
     $this->a_v = unserialize($this->_bs);
     Logger::log("[-] Unserialized: ".$this->_bs, $this);
     return 0;
   }


   private function _serialize() {
     $this->_bs = serialize($this->a_v);
     echo "[-] Serialized: ".$this->_bs."\n";
     return 0;
   }

   private function _encrypt() {
     if ($this->_bs) {
       $this->_ebs = base64_encode($this->_iv.mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->_key, $this->_bs, MCRYPT_MODE_CBC, $this->_iv));
       echo "[-] EBS: ".$this->_ebs."\n";
       return 0;
     }
     return 1;
   }

   private function _decrypt() {
     if ($this->_ebs) {
       $tmp = base64_decode($this->_ebs);
       $this->_iv = substr($tmp, 0, $this->_ivsize);
       $ct = substr($tmp, $this->_ivsize);
       $this->_bs = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->_key, $ct, MCRYPT_MODE_CBC, $this->_iv);
       Logger::log("[-] Decrypted: ".$this->_bs, $this);
     }
     return 1;
   }

   public function recv($sock) {
     $this->ebs = '';
     $len = socket_recvfrom($sock, $this->_ebs, MAX_MSG_SIZE, 0, $this->from, $this->port);
     Logger::log("Received: ".$this->_ebs, $this);
     $this->_decrypt();
     $this->_unserialize();
     Logger::log("Received $len bytes from ".$this->from.":".$this->port.": ".print_r($this->a_v, true), $this);
   }

   public function send($sock, $to, $port) {
     $this->_serialize();
     $this->_encrypt();
     return socket_sendto($sock, $this->_ebs, strlen($this->_ebs), 0, $to, $port);
   }
 }

?>
