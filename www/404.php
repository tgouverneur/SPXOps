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


 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $head->set('page', $page);

 $foot = new Template("../tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $content = new Template("../tpl/error.tpl");
 $content->set('error', "The page you requested has not been found...");

 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
