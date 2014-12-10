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

 $i = null;

 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$i) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 if (!isset($_POST['start']) ||
     !isset($_POST['what']) ||
     !isset($_POST['n'])) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 $start = $_POST['start'];
 $what = $_POST['what'];
 $n = $_POST['n'];

 if (!is_numeric($n)) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Error in the provided arguments';
   goto screen;
 }

 if (!$lm->o_login->cRight('SRV', R_VIEW)) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You don\'t have the rights to run this action';
   goto screen;
 }
 $obj = new RRD($i);
 if ($obj->fetchFromId()) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Cannot find RRD provided inside the database';
   goto screen;
 }

 try {
   $ret['res'] = $obj->getData($start, $what, $n);
   $ret['rc'] = 0;
 } catch (SPXException $e) {

   $ret['rc'] = 1;
   $ret['res'] = $e.toString();
   goto screen;
 }


screen:
 echo @json_encode($ret);

?>
