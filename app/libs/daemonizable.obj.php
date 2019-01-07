<?php
/**
 * Daemonizable object
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @subpackage device
 * @category classes
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */

interface Daemonizable
 {
 
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

?>
