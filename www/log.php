<?php
 require_once("../libs/utils.obj.php");
 require_once("../libs/config.inc.php");

 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = LoginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $page = array();
 $page['title'] = 'Add log for';
 $page['action'] = 'Add';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   HTTP::errWWW('You must be logged-in to access this page');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {

   $o_name = $_GET['w'];
   if (!class_exists($o_name) ||
       !method_exists($o_name, 'addLog')) {
     HTTP::errWWW('This kind of object doesn\'t support Logs');
   }

   if (!$lm->o_login->cRight($o_name::$RIGHT, R_EDIT)) {
     HTTP::errWWW('Access Denied, please check your access rights!');
   }
   if (!isset($_GET['i']) || empty($_GET['i'])) {
     HTTP::errWWW("No $o_name ID provided");
   }
   $obj = new $o_name($_GET['i']);
   if ($obj->fetchFromId()) {
     HTTP::errWWW("Object not found inside database");
   }
   $content = new Template('../tpl/form_log.tpl');
   $content->set('obj', $obj);
   $what = strtolower($o_name);
   $page['title'] .= ' '.$what;
   $content->set('page', $page);
   if (isset($_POST['submit'])) { /* clicked on the Add button */
     if (!isset($_POST['msg']) || empty($_POST['msg'])) {
       $content->set('error', 'No message specified');
       goto screen;
     }
     $obj->addLog($_POST['msg']);
     $content = new Template('../tpl/message.tpl');
     $content->set('msg', "Log entry for $what $obj has been added to database");
     $a_link = array(
          array('href' => '/view/w/'.$what.'/i/'.$obj->id,
                'name' => 'Back to '.$what,
               ),
          );
     goto screen;
   }

 } else {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "I don't know what to add a comment on...");
 }

screen:
 $head->set('page', $page);
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
