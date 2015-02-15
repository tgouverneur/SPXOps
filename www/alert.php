<?php
 require_once("../libs/utils.obj.php");


 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = LoginCM::getInstance();
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

   $content = new Template("../tpl/error.tpl");
   $content->set('error', "The page you requested cannot be called as-is...");

   $index->set('head', $head);
   $index->set('content', $content);
   $index->set('foot', $foot);
   echo $index->fetch();
   exit(0);
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$lm->o_login) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must be logged-in';
   goto screen;
 }

 $lm->o_login->fetchRights();

 if (!$lm->o_login->cRight('UGRP', R_EDIT)) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You don\'t have rights to modify group';
   goto screen;
 }

 $u = $a = $e = $g = 0;

 if (isset($_GET['u']) && !empty($_GET['u'])) {
   $u = $_GET['u'];
 }
 if (isset($_GET['a']) && !empty($_GET['a'])) {
   $a = $_GET['a'];
 }
 if (isset($_GET['e']) && !empty($_GET['e'])) {
   $e = $_GET['e'];
 }
 if (isset($_GET['g']) && !empty($_GET['g'])) {
   $g = $_GET['g'];
 }

 if ((!$g && !$a) || !$u) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 $ugroup = new UGroup();
 $ugroup->id = $u;
 if ($ugroup->fetchFromId()) {
   $ret['rc'] = 1;
   $ret['msg'] = 'UGroup specified not found in database';
   goto screen;
 }

 if ($a) {
   $at = new AlertType($a);
   if ($at->fetchFromId()) {
     $ret['rc'] = 1;
     $ret['msg'] = 'AlertType specified not found in database';
     goto screen;
   }

   if (!is_numeric($e) && $e) {
     $ret['rc'] = 1;
     $ret['msg'] = 'Incorrect level specification';
     goto screen;
   }

   $at->fetchJT('a_ugroup');
   
   if ($e && !$at->isInJT('a_ugroup', $ugroup, array())) {
     $at->addToJT('a_ugroup', $ugroup);
   } else if (!$e && $at->isInJT('a_ugroup', $ugroup, array())) {
     $at->delFromJT('a_ugroup', $ugroup);
   }

   Act::add("Changed the alert $at for group $ugroup ($e)", $lm->o_login);
   $ret['rc'] = 0;
   $ret['msg'] = "The alert $at for $ugroup has been set to $e.";
   goto screen;
 } else if ($g) {
   $sg = new SGroup($g);
   if ($sg->fetchFromId()) {
     $ret['rc'] = 1;
     $ret['msg'] = 'SGroup specified not found in database';
     goto screen;
   }

   if (!is_numeric($e) && $e) {
     $ret['rc'] = 1;
     $ret['msg'] = 'Incorrect level specification';
     goto screen;
   }

   $sg->fetchJT('a_ugroup');
   
   if ($e && !$sg->isInJT('a_ugroup', $ugroup, array())) {
     $sg->addToJT('a_ugroup', $ugroup);
   } else if (!$e && $sg->isInJT('a_ugroup', $ugroup, array())) {
     $sg->delFromJT('a_ugroup', $ugroup);
   }

   Act::add("Changed the alert on server group $sg for group $ugroup ($e)", $lm->o_login);
   $ret['rc'] = 0;
   $ret['msg'] = "The alert on server group $sg for $ugroup has been set to $e.";
   goto screen;
 }

screen:
 echo json_encode($ret);

?>
