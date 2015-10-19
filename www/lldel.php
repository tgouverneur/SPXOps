<?php
/**
 * Remove object from list
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage management
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

 if (!$h->isAjax()) {
     throw new ExitException('The page you requested cannot be called as-is...', 1);
 }

 if (!$lm->o_login) {
     throw new ExitException('You must be logged-in', 2);
 }
 $lm->o_login->fetchRights();

 $w = $i = $o = $t = $r = null;

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   $w = $_GET['w'];
 }
 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }
 if (isset($_GET['o']) && !empty($_GET['o'])) {
   $o = $_GET['o'];
 }
 if (isset($_GET['t']) && !empty($_GET['t'])) {
   $t = $_GET['t'];
 }
 if (isset($_GET['r']) && !empty($_GET['r'])) {
   $r = $_GET['r'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$w || !$i || !$o || !$t) {
    throw new ExitException('You must provide proper arguments', 2);
 }

 switch ($w) {
   case 'login':
     if (!$lm->o_login->cRight('USR', R_EDIT)) {
         throw new ExitException('You don\'t have the rights to edit users');
     }
     $obj = new Login($i);
     if ($obj->fetchFromId()) {
         throw new ExitException('Cannot find User provided inside the database');
     }
     if (!strcmp($o, 'ugroup')) {
       $obj->fetchJT('a_ugroup');
       $tobj = new UGroup($t);
       if ($tobj->fetchFromId()) {
         throw new ExitException('Cannot find User Group provided inside the database');
        }
        if ($obj->isInJT('a_ugroup', $tobj)) {
          $obj->delFromJT('a_ugroup', $tobj);
          Act::add("Removed User group $tobj from user $obj", $lm->o_login);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = 'ugroup';
          $ret['msg'] = "Removed group $tobj from user $obj";

        } else {
          throw new ExitException('Specified group is not in this user');
        }
     } else {
       throw new ExitException('Unrecognized target class');
     }
   break;
   case 'check':
     if (!$lm->o_login->cRight('CHK', R_EDIT)) {
       throw new ExitException('You don\'t have the rights to edit check');
     }
     $obj = new Check($i);
     if ($obj->fetchFromId()) {
       throw new ExitException('Cannot find Check provided inside the databse');
     }
     if (!strcmp($o, 'sgroup') || !strcmp($o, 'esgroup')) {
       $obj->fetchJT('a_sgroup');
       $tobj = new SGroup($t);
       if ($tobj->fetchFromId()) {
         throw new ExitException('Cannot find Server Group provided inside the databse');
        }
        if ($obj->isInJT('a_sgroup', $tobj)) {
          $obj->delFromJT('a_sgroup', $tobj);
          Act::add("Removed Server group $tobj from check $obj", $lm->o_login);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = $o;
          $ret['msg'] = "Removed group $tobj from check $obj";

        } else {
          throw new ExitException('Specified group is not in this check');
        }
     } else {
       throw new ExitException('Unrecognized target class');
     }
   break;
   case 'ugroup':
     if (!$lm->o_login->cRight('UGRP', R_EDIT)) {
       throw new ExitException('You don\'t have the rights to edit user group');
     }
     $obj = new UGroup($i);
     if ($obj->fetchFromId()) {
       throw new ExitException('Cannot find User Group provided inside the database');
     }
     if (!strcmp($o)) {
       $obj->fetchJT('a_login');
       $tobj = new Login($t);
       if ($tobj->fetchFromId()) {
         throw new ExitException('Cannot find Login provided inside the database');
	}
        if ($obj->isInJT('a_login', $tobj)) {
          $obj->delFromJT('a_login', $tobj);
          Act::add("Removed login $tobj from $obj group", $lm->o_login);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = 'login';
          $ret['msg'] = "Removed login $tobj from $obj group";

        } else {
          throw new ExitException('Specified login is not in this group');
        }
     } else {
       throw new ExitException('Unrecognized target class');
     }
   break;
   case 'sgroup':
     if (!$lm->o_login->cRight('SRVGRP', R_EDIT)) {
       throw new ExitException('You don\'t have the rights to edit server group');
     }
     $obj = new SGroup($i);
     if ($obj->fetchFromId()) {
       throw new ExitException('Cannot find Server Group provided inside the database');
     }
     if (!strcmp($o, 'server')) {
       $obj->fetchJT('a_server');
       $tobj = new Server($t);
       if ($tobj->fetchFromId()) {
         throw new ExitException('Cannot find Server provided inside the database');
        }
        if ($obj->isInJT('a_server', $tobj)) {
          $obj->delFromJT('a_server', $tobj);
          Act::add("Removed server $tobj from $obj group", $lm->o_login);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = 'server';
          $ret['msg'] = "Removed server $tobj from $obj group";

        } else {
          throw new ExitException('Specified server is not in this group');
        }
     } else if (!strcmp($o, 'vm')) {
       $obj->fetchJT('a_vm');
       $tobj = new VM($t);
       if ($tobj->fetchFromId()) {
         throw new ExitException('Cannot find Server provided inside the database');
        }
        if ($obj->isInJT('a_vm', $tobj)) {
          $obj->delFromJT('a_vm', $tobj);
          Act::add("Removed vm $tobj from $obj group", $lm->o_login);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = 'vm';
          $ret['msg'] = "Removed vm $tobj from $obj group";

        } else {
          throw new ExitException('Specified vm is not in this group');
        }
     } else {
       throw new ExitException('Unrecognized target class');
     }
   break;
   default:
       throw new ExitException('Unknown class provided');
   break;
 }

 echo json_encode($ret);

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
