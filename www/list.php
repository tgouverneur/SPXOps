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
     case 'sgroup':
       $a_list = SGroup::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login && $lm->o_login->f_admin) {
         $content->set('canDel', true);
         $content->set('canMod', true);
         $actions = array(
                        'Add' => '/add/w/sgroup',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Server Groups');
       $content->set('oc', 'SGroup');
       $page['title'] .= 'Server Group';
     break;
     case 'ugroup':
       $a_list = UGroup::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login && $lm->o_login->f_admin) {
         $content->set('canDel', true);
         $content->set('canMod', true);
         $actions = array(
                        'Add' => '/add/w/ugroup',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'User Groups');
       $content->set('oc', 'UGroup');
       $page['title'] .= 'User Group';
     break;
     case 'check':
       $a_list = Check::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login && $lm->o_login->f_admin) {
         $content->set('canDel', true);
         $content->set('canMod', true);
         $actions = array(
                        'Add' => '/add/w/check',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Checks');
       $content->set('oc', 'Check');
       $page['title'] .= 'Checks';
     break;
     case 'pserver':
       $a_list = PServer::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login) {
         $content->set('canDel', true);
         $actions = array(
                        'Add' => '/add/w/pserver',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Physical Servers');
       $content->set('oc', 'PServer');
       $page['title'] .= 'Physical Servers';
     break;
     case 'server':
       $a_list = Server::getAll(true, array(), array('ASC:hostname'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login) {
         $content->set('canDel', true);
         $content->set('canMod', true);
         $actions = array( 
                        'Add' => '/add/w/server',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Servers');
       $content->set('oc', 'Server');
       $page['title'] .= 'Servers';
     break;
     case 'cluster':
       $a_list = Cluster::getAll(true, array(), array('ASC:name'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       if ($lm->o_login) {
         $content->set('canDel', true);
         $content->set('canMod', true);
         $actions = array(
                        'Add' => '/add/w/cluster',
                    );
         $content->set('actions', $actions);
       }
       $content->set('canView', true);
       $content->set('what', 'Clusters');
       $content->set('oc', 'Cluster');
       $page['title'] .= 'Clusters';
     break;
     case 'act':
       $a_list = Act::getAll(true, array(), array('DESC:t_add'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('what', 'Activities');
       $content->set('oc', 'Act');
       $page['title'] .= 'Activities';
     break;
     case 'jobs':
       if (!$lm->o_login) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You should be logged in to access this page...");
         goto screen;
       }
       $a_list = Job::getAll(true, array(), array('DESC:t_upd'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       if ($lm->o_login->f_admin) {
         $content->set('canDel', true);
       }
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
     case 'susers':
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
       $a_list = SUser::getAll(true, array(), array('ASC:username'));
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('canMod', true);
       $content->set('canDel', true);
       $actions = array(
                      'Add' => '/add/w/suser',
                  );
       $content->set('actions', $actions);
       $content->set('what', 'SSH Users');
       $content->set('oc', 'SUser');
       $page['title'] .= 'SSH Users';
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
