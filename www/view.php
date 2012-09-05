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

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 //$foot->set("start_time", $start_time);
 $page = array();
 $page['title'] = 'Viewing ';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'pserver':
       $what = 'Physical Server';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new PServer($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $content = new Template('../tpl/view_pserver.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
     break;
     case 'check':
       $what = 'Check';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new Check($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $content = new Template('../tpl/view_check.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
     break;
     case 'sgroup':
       $what = 'Server Group';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new SGroup($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchJT('a_server');
       $content = new Template('../tpl/view_sgroup.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $content->set('a_server', Server::getAll(true, array(), array('ASC:hostname')));
       $js = array('llist.js');
       $foot->set('js', $js);
     break;
     case 'ugroup':
       $what = 'User Group';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new UGroup($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchJT('a_login');
       $content = new Template('../tpl/view_ugroup.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $content->set('a_login', Login::getAll(true, array(), array('ASC:username')));
       $js = array('llist.js');
       $foot->set('js', $js);
     break;
     case 'server':
       $what = 'Server';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       } 
       $obj = new Server($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchAll(1);
       $content = new Template('../tpl/view_server.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('jobs.js');
       $foot->set('js', $js);
     break;
     case 'cluster':
       $what = 'Cluster';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new Cluster($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchAll(1);
       $content = new Template('../tpl/view_cluster.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('jobs.js');
       $foot->set('js', $js);
     break;
     case 'job':
       $what = 'Job';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new Job($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchAll(1);
       $content = new Template('../tpl/view_job.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
     break;
     default:
       $content = new Template('../tpl/error.tpl');
       $content->set('error', 'Unknown option or not yet implemented');
     break;
   }
 } else {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "I don't know what to list...");
 }

screen:
 $head->set('page', $page);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
