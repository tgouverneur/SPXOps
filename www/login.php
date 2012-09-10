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
 $content = new Template("../tpl/login.tpl");

 if (isset($_POST['submit'])) {
   $username = $password = '';
   if (isset($_POST['username'])) $username = $_POST['username'];
   if (isset($_POST['password'])) $password = $_POST['password'];
   $remember = false;
   $errors = array();
   if (isset($_POST['remember'])) {
     $remember = true;
   }
   if (empty($username)) {
     $errors[] = 'Empty username';
   }
   if (empty($password)) {
     $errors[] = 'Empty password';
   }
   if (count($errors) || ($rc = $lm->login($username, $password, $remember))) {
     $errors[] = 'Unable to authenticate your username and password, please check';
   } else {
     $page['login'] = $lm->o_login;
     $head->set('page', $page);
     $content = new Template('../tpl/message.tpl');
     $content->set('msg', "Welcome ".$lm->o_login->fullname.", you are successfully logged in");
     goto screen;
   }
   $content->set('error', $errors);
 }

screen:
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
