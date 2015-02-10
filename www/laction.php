<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = MySqlCM::getInstance();
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

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$w || !$i) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 if (!$lm->o_login->cRight('SRV', R_EDIT)) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You don\'t have the rights to run this action';
   goto screen;
 }
 $obj = new Server($i);
 if ($obj->fetchFromId()) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Cannot find Server provided inside the database';
   goto screen;
 }

 $obj->fetchFK('fk_os');
 if (!$obj->o_os) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Cannot find OS for the provided Server';
   goto screen;
 }

 $eas = $obj->getExtraActions();
 $action = null;
 foreach($eas as $a) {
   if (!strcmp($a->fct, $w)) {
     $action = $a;
     break;
   }
 }
 if (!$action) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Cannot find this action';
   goto screen;
 }

 $ret['rc'] = $action->call($obj);
 $ret['res'] = $action->res;
 goto screen;

screen:
 echo json_encode($ret);

?>
