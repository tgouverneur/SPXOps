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
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   HTTP::errWWW('You must be logged-in to access this page');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'suser':
       /**
        * @TODO; Check dependancies before delete()ing
        */
       if (!$lm->o_login->cRight('CUSER', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
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
       Act::add('Deleted the SSH User: '.$obj->username, $lm->o_login);
       $a_link = array(
              array('href' => '/list/w/susers',
                    'name' => 'Back to list of connect users',
                   ),
              );
       goto screen;
     break;
     case 'pserver':
       /**
	* @TODO; Check dependancies before delete()ing
	*/
       if (!$lm->o_login->cRight('PHY', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
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
       Act::add('Deleted the Physical Server: '.$obj->name, $lm->o_login);
       $a_link = array(
              array('href' => '/list/w/pserver',
                    'name' => 'Back to list of physical server',
                   ),
              );
       goto screen;
     break;
     case 'check':
       /**
	* @TODO; Check dependancies before delete()ing
	*/
       if (!$lm->o_login->cRight('CHK', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
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
       Act::add('Deleted the Check: '.$obj->name, $lm->o_login);
       $a_link = array(
              array('href' => '/list/w/check',
                    'name' => 'Back to list of checks',
                   ),
              );
       goto screen;
     break;
     case 'rjob':
       if (!$lm->o_login->cRight('RJOB', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
       $what = 'RJob';
       $page['title'] .= $what;
       $obj = new RJob();
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $obj->id = $_GET['i'];
         if ($obj->fetchFromId()) {
           $content = new Template('../tpl/error.tpl');
           $content->set('error', "Recurrent Job specified cannot be found in the database");
           goto screen;
         }
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Recurrent Job not specified");
         goto screen;
       }
       $content = new Template('../tpl/message.tpl');
       $content->set('msg', "Recurrent Job $obj has been removed from database");
       $obj->delete();
       Act::add('Deleted the Recurrent Job: '.$obj->name, $lm->o_login);
       $a_link = array(
              array('href' => '/list/w/rjob',
                    'name' => 'Back to list of recurrent jobs',
                   ),
              );
       goto screen;
     break;
     case 'ugroup':
       /**
        * @TODO; Check dependancies before delete()ing
        */
       if (!$lm->o_login->cRight('UGRP', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
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
       Act::add('Deleted the User Group: '.$obj->name, $lm->o_login);
       $a_link = array(
              array('href' => '/list/w/ugroup',
                    'name' => 'Back to list of user groups',
                   ),
              );

       goto screen;
     break;
     case 'sgroup':
       /**
        * @TODO; Check dependancies before delete()ing
        */
       if (!$lm->o_login->cRight('SRVGRP', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
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
       Act::add('Deleted the Server Group: '.$obj->name, $lm->o_login);
       $a_link = array(
              array('href' => '/list/w/sgroup',
                    'name' => 'Back to list of server groups',
                   ),
              );
       goto screen;
     break;
     case 'cluster':
       /**
	* @TODO; Check dependancies before delete()ing
	*/
       if (!$lm->o_login->cRight('CLUSTER', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
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
       Act::add('Deleted the Cluster: '.$obj->name, $lm->o_login);
       $a_link = array(
              array('href' => '/list/w/cluster',
                    'name' => 'Back to list of clusters',
                   ),
              );
       goto screen;
     break;
     case 'server':
       /**
	* @TODO; Check dependancies before delete()ing
	*/
       if (!$lm->o_login->cRight('SRV', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
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
       Act::add('Deleted the Server: '.$obj->hostname, $lm->o_login);
       $a_link = array(
              array('href' => '/list/w/server',
                    'name' => 'Back to list of servers',
                   ),
              );
       goto screen;
     case 'login':
       if (!$lm->o_login->cRight('USR', R_DEL)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
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
       Act::add('Deleted the User: '.$obj->username, $lm->o_login);
       $a_link = array(
            array('href' => '/list/w/users',
                  'name' => 'Back to list of users',
                 ),
            );
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
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
