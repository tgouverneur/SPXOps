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

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'server':
       if (!isset($_GET['s']) || empty($_GET['s'])) {
	  die('Missing argument');
       }
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
         die('Missing argument');
       }
       $id = $_GET['i'];
       $a_server = Server::getAll(true, array('fk_os' => 'CST:'.$id), array('hostname'));
       header('Content-Type: application/json');
       echo json_encode($a_server);
     break;
     default:
       die('Unknown option or not implemented');
     break;
   }
 }

?>
