<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $loggedout = false;
 if ($lm->o_login) {
   $lm->logout();
   $loggedout = true;
 }

 $h = HTTP::getInstance();
 $h->parseUrl();

 /* Page setup */
 $page = array();
 $page['title'] = 'Home';

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $head->set('page', $page);

 $foot = new Template("../tpl/foot.tpl");

 if ($loggedout) {
   $content = new Template("../tpl/message.tpl");
   $content->set('msg', "You have been successfully logged out.");
   goto screen;
 }
 
 $content = new Template("../tpl/error.tpl");
 $content->set('error', "To logout, you should login first ;-)");

screen:
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
