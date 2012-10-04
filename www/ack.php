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
 if (!$lm->o_login->cRight('CHKBOARD', R_DEL)) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You don\'t have the rights to ack check';
   goto screen;
 }

 $i = $n = null;

 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }
 if (isset($_GET['n']) && !empty($_GET['n'])) {
   $n = $_GET['n'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$i) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 $r = new Result($i);
 if ($r->fetchFromId()) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Result specified not found in database';
   goto screen;
 }
 if ($r->f_ack && !$n) {
   $ret['rc'] = 1;
   $ret['msg'] = 'This result is already acknowledged';
   goto screen;
 }
 if (!$r->f_ack && $n) {
   $ret['rc'] = 1;
   $ret['msg'] = 'This result is not acknowledged';
   goto screen;
 }
 if (!$n) {
   $r->f_ack = 1;
   $r->fk_login = $lm->o_login->id;
   $r->o_login = $lm->o_login;
   $a = Act::add("Acknowledged $r", $lm->o_login);
   $ret['msg'] = "$r successfully acknowledged";
   $ret['ackWho'] = $r->o_login->username;
   $ret['ackId'] = $r->fk_login;
 } else {
   $r->f_ack = 0;
   $r->fk_login = -1;
   $k->o_login = null;
   $a = Act::add("Un-Acknowledged $r", $lm->o_login);
   $ret['msg'] = "$r successfully unacknowledged";
 }
 $ret['rc'] = 0;
 $ret['id'] = $r->id;
 $r->update();
 goto screen;

screen:
 echo json_encode($ret);

?>
