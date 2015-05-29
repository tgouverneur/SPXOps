<?php
/**
 * API
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage JSON
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
 require_once("../libs/utils.obj.php");

try {

 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   throw new ExitException('An error has occurred with the SQL Server and we were unable to process your request...');
 }
 $lm = LoginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
     throw new ExitException('You must be logged-in', 2);
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'lsVM':
       if (isset($_GET['o']) && !empty($_GET['o'])) {
         $o = $_GET['o'];
       } 
       $a_vms = VM::getAll(true, array('name' => 'LIKE:'.$o), array('ASC:hostname'));
       $ret = array();
       $ret['count'] = count($a_vms);
       $ret['vms'] = array();
       foreach($a_vms as $vm) {
           $ret['vms'][$vm->name] = $vm->jsonSerialize();
       }
       header('Content-Type: application/json');
       echo json_encode($a_s);
     break;
     case 'server':
       $o = null;
       $i = null;
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $i = $_GET['i'];
       } 
       if (isset($_GET['o']) && !empty($_GET['o'])) {
         $o = $_GET['o'];
       } 
       $abj = new Server();
       if ($i) {
           $obj->id = $i;
           if ($obj->fetchFromId()) {
               throw new ExitException('No server found with that ID')
           }
       } else if ($o) {
           $obj->hostname = $o;
           if ($obj->fetchFromField('hostname')) {
               throw new ExitException('No server found with that hostname')
           }
       } else {
           throw new ExitException('No server hostname/id provided')
       }
       header('Content-Type: application/json');
       echo json_encode($obj->jsonSerialize());
     break;
     default:
       throw new ExitException('Unknown option or not implemented', 2);
     break;
   }
 }

} catch (ExitException $e) {

    if ($e->type == 2) {
        echo Utils::getJSONError($e->getMessage());
    } else {
        $h = Utils::getHTTPError($e->getMessage());
        echo $h->fetch();
    }

} catch (Exception $e) {
    /* @TODO: LOG EXCEPTION */
    $h = Utils::getHTTPError('Unexpected Exception');
    echo $h->fetch();
}


?>
