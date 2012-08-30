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
 $page['title'] = 'List of ';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'pserver':
       $a_list = PServer::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canDel', true);
       $content->set('canView', true);
       $content->set('what', 'Physical Servers');
       $content->set('oc', 'PServer');
       $page['title'] .= 'Physical Servers';
     break;
     case 'server':
       $a_list = Server::getAll(true, array(), array('ASC:hostname'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canMod', true);
       $content->set('canDel', true);
       $content->set('canView', true);
       $content->set('what', 'Servers');
       $content->set('oc', 'Server');
       $page['title'] .= 'Servers';
     break;
     case 'jobs':
       $a_list = Job::getAll(true, array(), array('DESC:t_upd'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('canMod', true);
       $content->set('canDel', true);
       $content->set('what', 'Jobs');
       $content->set('oc', 'Job');
       $page['title'] .= 'Jobs';
     break;
     case 'users':
       if (!$lm->o_login) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You should be logged in to access this page...");
         goto screen;
       }
       $a_list = Login::getAll(true, array(), array('ASC:username'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       if ($lm->o_login->f_admin) {
         $content->set('canMod', true);
         $content->set('canDel', true);
         $actions = array(
			'Add' => '/add/w/user',
	 	    );
	 $content->set('actions', $actions);
       }
       $content->set('what', 'Users');
       $content->set('oc', 'Login');
       $page['title'] .= 'Users';
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
