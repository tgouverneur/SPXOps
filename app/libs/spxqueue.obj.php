<?php
 /**
  * SPXQueue object
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
 class SPXQueue
 {
   private $_qid = null;
     private $_qr = null;
     private $_msize = 65536; // 64k by default

   public function getMsize()
   {
       return $this->_msize;
   }
     public function setMsize($size)
     {
         $this->_msize = $size;
     }

     public function __construct($qid)
     {
         $this->_qid = $qid;
         $this->_qr = msg_get_queue($this->_qid);
     }

     public function receive(&$msg)
     {
         $type = null;
         $str = '';
         if (msg_receive($this->_qr, 1, $type, $this->_msize, $str, false)) {
             $msg = unserialize($str);

             return true;
         }

         return false;
     }

     public function stat()
     {
         return msg_stat_queue($this->_qr);
     }

     public function send($msg)
     {
         $rc = 0;
         if (msg_send($this->_qr, 1, serialize($msg), false, true, $rc)) {
             return true;
         }
         Logger::log("sendmsg fail: $rc", LLOG_DEBUG);

         return false;
     }

     public function destroy()
     {
         return msg_remove_queue($this->_qr);
     }
 }
