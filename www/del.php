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
 $page['title'] = 'Remove ';
 $page['action'] = 'Remove';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (!$lm->o_login) {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "You should be logged in to access this page...");
   goto screen;
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'suser':
       /**
        * @TODO; Check dependancies before delete()ing
        */
       $what = 'SSH User';
       $page['title'] .= $what;
       $obj = new SUser();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
	 $obj->id = $_GET['i'];
	 if ($obj->fetchFromId()) {
	   $content = new Template('../tpl/error.tpl');
	   $content->set('error', "SSH User specified cannot be found in the database");
	   goto screen;
	 }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "SSH User not specified");
         goto screen;
       }
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "SSH User $obj has been removed from database");
       $obj->delete();
       $a = Act::add('Deleted the SSH User: '.$obj->username, 'login', $lm->o_login);
       goto screen;
     break;
     case 'pserver':
       /**
	* @TODO; Check dependancies before delete()ing
	*/
       $what = 'Physical Server';
       $page['title'] .= $what;
       $obj = new PServer();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "Physical Server specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Physical Server not specified");
         goto screen;
       }
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "Physical Server $obj has been removed from database");
       $obj->delete();
       $a = Act::add('Deleted the Physical Server: '.$obj->name, 'login', $lm->o_login);
       goto screen;
     break;
     case 'check':
       /**
	* @TODO; Check dependancies before delete()ing
	*/
       $what = 'Check';
       $page['title'] .= $what;
       $obj = new Check();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "Check specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Check not specified");
         goto screen;
       }
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "Check $obj has been removed from database");
       $obj->delete();
       $a = Act::add('Deleted the Check: '.$obj->name, 'login', $lm->o_login);
       goto screen;
     break;
     case 'rjob':
       $what = 'RJob';
       $page['title'] .= $what;
       $obj = new RJob();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "Check specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Check not specified");
         goto screen;
       }
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "Check $obj has been removed from database");
       $obj->delete();
       $a = Act::add('Deleted the Check: '.$obj->name, 'login', $lm->o_login);
       goto screen;
     break;
     case 'ugroup':
       /**
        * @TODO; Check dependancies before delete()ing
        */
       $what = 'User Group';
       $page['title'] .= $what;
       $obj = new UGroup();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "User Group specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "User Group not specified");
         goto screen;
       }
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "User Group $obj has been removed from database");
       $obj->delete();
       $a = Act::add('Deleted the User Group: '.$obj->name, 'login', $lm->o_login);
       goto screen;
     break;
     case 'sgroup':
       /**
        * @TODO; Check dependancies before delete()ing
        */
       $what = 'Server Group';
       $page['title'] .= $what;
       $obj = new SGroup();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "Server Group specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Server Group not specified");
         goto screen;
       }
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "Server Group $obj has been removed from database");
       $obj->delete();
       $a = Act::add('Deleted the Server Group: '.$obj->name, 'login', $lm->o_login);
       goto screen;
     break;
     case 'cluster':
       /**
	* @TODO; Check dependancies before delete()ing
	*/
       $what = 'Cluster';
       $obj = new Cluster();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "Cluster specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Cluster not specified");
         goto screen;
       }
       $page['title'] .= $what;
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "Cluster $obj has been removed from database");
       $obj->delete();
       $a = Act::add('Deleted the Cluster: '.$obj->name, 'login', $lm->o_login);
       goto screen;
     break;
     case 'server':
       /**
	* @TODO; Check dependancies before delete()ing
	*/
       $what = 'Server';
       $obj = new Server();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "Server specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Server not specified");
         goto screen;
       }
       $page['title'] .= $what;
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "Server $obj has been removed from database");
       $obj->delete();
       $a = Act::add('Deleted the Server: '.$obj->hostname, 'login', $lm->o_login);
       goto screen;
     case 'user':
       if (!$lm->o_login->f_admin) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You should be administrator in to access this page...");
         goto screen;
       }
       $what = 'User';
       $obj = new Login();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "User specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "User not specified");
         goto screen;
       }
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "User $obj has been removed from database");
       $page['title'] .= $what;
       $obj->delete();
       $a = Act::add('Deleted the User: '.$obj->username, 'login', $lm->o_login);
       goto screen;
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
