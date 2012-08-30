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

 $c = $f = $a = null;

 if (isset($_GET['c']) && !empty($_GET['c'])) {
   $c = $_GET['c'];
 }
 if (isset($_GET['f']) && !empty($_GET['f'])) {
   $f = $_GET['f'];
 }
 if (isset($_GET['a']) && !empty($_GET['a'])) {
   $a = $_GET['a'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$c || !$f || !$a) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 switch ($c) {
   case 'Update':
     if ($f == 'jobServer') {
       $s = new Server($a);
       if ($s->fetchFromId()) {
         $ret['rc'] = 1;
	 $ret['msg'] = 'Server specified not found in database';
	 goto screen;
       }
       $j = new Job();
       $j->class = $c;
       $j->fct = $f;
       $j->arg = $a;
       $j->state = S_NEW;
       $j->insert();
       /* @TODO: Add log entry */
       $ret['rc'] = 0;
       $ret['msg'] = "Job to update server $s has been succesfully added to the queue...";
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