<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 if (!$h->isAjax()) {
   /* Page setup */
   $page = array();
   $page['title'] = 'Error';
   if ($lm->o_login) $page['login'] = &$lm->o_login;

   $index = new Template("../tpl/index.tpl");
   $head = new Template("../tpl/head.tpl");
   $head->set('page', $page);
   $foot = new Template("../tpl/foot.tpl");
   $foot->set("start_time", $start_time);

   $content = new Template("../tpl/error.tpl");
   $content->set('error', "The page you requested cannot be called as-is...");

   $index->set('head', $head);
   $index->set('content', $content);
   $index->set('foot', $foot);
   echo $index->fetch();
   exit(0);
 }


 if (!$lm->o_login) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must be logged-in';
   goto screen;
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
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 switch ($w) {
   case 'login':
     if (!$lm->o_login->cRight('USR', R_EDIT)) {
       $ret['rc'] = 1;
       $ret['msg'] = 'You don\'t have the rights to edit user';
       goto screen;
     }
     $obj = new Login($i);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find User provided inside the database';
       goto screen;
     }
     if (!strcmp($o, 'ugroup')) {
       $obj->fetchJT('a_ugroup');
       $tobj = new UGroup($t);
       if ($tobj->fetchFromId()) {
         $ret['rc'] = 1;
         $ret['msg'] = 'Cannot find User Group provided inside the database';
         goto screen;
        }
        if ($obj->isInJT('a_ugroup', $tobj)) {
          $obj->delFromJT('a_ugroup', $tobj);
          $a = Act::add("Removed User group $tobj from user $obj", 'login', $lm->o_login);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = 'ugroup';
          $ret['msg'] = "Removed group $tobj from user $obj";
          goto screen;

        } else {
          $ret['rc'] = 1;
          $ret['msg'] = 'Specified group is not in this user';
          goto screen;
        }
     } else {
       $ret['rc'] = 1;
       $ret['msg'] = 'Unrecognized target class';
       goto screen;
     }
   break;
   case 'check':
     if (!$lm->o_login->cRight('CHK', R_EDIT)) {
       $ret['rc'] = 1;
       $ret['msg'] = 'You don\'t have the rights to edit check';
       goto screen;
     }
     $obj = new Check($i);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find Check provided inside the database';
       goto screen;
     }
     if (!strcmp($o, 'sgroup') || !strcmp($o, 'esgroup')) {
       $obj->fetchJT('a_sgroup');
       $tobj = new SGroup($t);
       if ($tobj->fetchFromId()) {
         $ret['rc'] = 1;
         $ret['msg'] = 'Cannot find Server Group provided inside the database';
         goto screen;
        }
        if ($obj->isInJT('a_sgroup', $tobj)) {
          $obj->delFromJT('a_sgroup', $tobj);
          $a = Act::add("Removed Server group $tobj from check $obj", 'check', $lm->o_check);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = $o;
          $ret['msg'] = "Removed group $tobj from check $obj";
          goto screen;

        } else {
          $ret['rc'] = 1;
          $ret['msg'] = 'Specified group is not in this check';
          goto screen;
        }
     } else {
       $ret['rc'] = 1;
       $ret['msg'] = 'Unrecognized target class';
       goto screen;
     }
   break;
   case 'ugroup':
     if (!$lm->o_login->cRight('UGRP', R_EDIT)) {
       $ret['rc'] = 1;
       $ret['msg'] = 'You don\'t have the rights to edit user group';
       goto screen;
     }
     $obj = new UGroup($i);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find User Group provided inside the database';
       goto screen;
     }
     if (!strcmp($o, 'login')) {
       $obj->fetchJT('a_login');
       $tobj = new Login($t);
       if ($tobj->fetchFromId()) {
         $ret['rc'] = 1;
         $ret['msg'] = 'Cannot find Login provided inside the database';
         goto screen;
	}
        if ($obj->isInJT('a_login', $tobj)) {
          $obj->delFromJT('a_login', $tobj);
	  $a = Act::add("Removed login $tobj from $obj group", 'login', $lm->o_login);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = 'login';
          $ret['msg'] = "Removed login $tobj from $obj group";
          goto screen;

        } else {
	  $ret['rc'] = 1;
          $ret['msg'] = 'Specified login is not in this group';
          goto screen;
	}
     } else {
       $ret['rc'] = 1;
       $ret['msg'] = 'Unrecognized target class';
       goto screen;
     }
   break;
   case 'sgroup':
     if (!$lm->o_login->cRight('SRVGRP', R_EDIT)) {
       $ret['rc'] = 1;
       $ret['msg'] = 'You don\'t have the rights to edit server group';
       goto screen;
     }
     $obj = new SGroup($i);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find Server Group provided inside the database';
       goto screen;
     }
     if (!strcmp($o, 'server')) {
       $obj->fetchJT('a_server');
       $tobj = new Server($t);
       if ($tobj->fetchFromId()) {
         $ret['rc'] = 1;
         $ret['msg'] = 'Cannot find Server provided inside the database';
         goto screen;
        }
        if ($obj->isInJT('a_server', $tobj)) {
          $obj->delFromJT('a_server', $tobj);
          $a = Act::add("Removed server $tobj from $obj group", 'login', $lm->o_login);
          $ret['rc'] = 0;
          $ret['id'] = $tobj->id;
          $ret['llist'] = 'server';
          $ret['msg'] = "Removed server $tobj from $obj group";
          goto screen;

        } else {
          $ret['rc'] = 1;
          $ret['msg'] = 'Specified server is not in this group';
          goto screen;
        }
     } else {
       $ret['rc'] = 1;
       $ret['msg'] = 'Unrecognized target class';
       goto screen;
     }
   break;
   default:
     $ret['rc'] = 1;
     $ret['msg'] = 'Unkown class provided';
     goto screen;
   break;
 }

screen:
 echo json_encode($ret);

?>
