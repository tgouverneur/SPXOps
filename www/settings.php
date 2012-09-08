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
 $page = array();
 $page['title'] = 'Edit settings';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (!$lm->o_login) {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "You should be logged in to access this page...");
   goto screen;
 }

 if (!$lm->o_login->f_admin) {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "You should be administrator to access this page...");
   goto screen;
 }

 $what = 'Setting';
 $content = new Template('../tpl/settings.tpl');
 Setting::fetchAll();
 $a_cat = Setting::getCat();
 $content->set('a_cat', $a_cat);
 $page['title'] .= $what;
 $content->set('page', $page);
 if (isset($_POST['submit'])) { /* clicked on the Edit button */
   $content->set('msg', "Settings have been updated");
   goto screen;
 }


screen:
 $head->set('page', $page);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>