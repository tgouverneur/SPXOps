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

 /* Page setup */
 $page = array();
 $page['title'] = 'Home';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $head->set('page', $page);
 $foot = new Template("../tpl/foot.tpl");

 if (!$lm->o_login) {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "You should be logged in to access this page...");
   goto screen;
 }

 $i = null;
 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }

 if ($i) {
   $obj = new Server($i);
   if ($obj->fetchFromId()) {
     $content = new Template('../tpl/error.tpl');
     $content->set('error', "You should be logged in to access this page...");
     goto screen;
   }
   $obj->fetchJT('a_sgroup');
   $obj->buildCheckList(true);
   $content = new Template("../tpl/dashboard_server.tpl");
   $content->set('obj', $obj);
   $a_link = array(
	array('href' => '/dashboard',
	      'name' => 'Back to dashboard',
	     ),
        array('href' => '/view/w/server/i/'.$obj->id,
              'name' => 'Back to server',
             ),
	);
   $js = array('check.js');
   $foot->set('js', $js);
 } else {
   $content = new Template("../tpl/dashboard.tpl");
   $content->set('a_list', Server::dashboardArray());
 }

screen:

 if (isset($a_link)) $foot->set('a_link', $a_link);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
