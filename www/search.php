<?php
 require_once("../libs/autoload.lib.php");
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
 $page['title'] = 'List of ';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'pserver':
       if (!isset($_POST['q']) || empty($_POST['q'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'Unknown option or not yet implemented');
         goto screen;
       }
       $q = '%'.$_POST['q'].'%';
       $f = array();
       $s = array('ASC:name');
       $f['name'] = 'LIKE:'.$q;
       $a_list = PServer::getAll(true, $f, $s);
       if (!count($a_list)) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'No result found for your search...');
         goto screen;
       }
       if (count($a_list) == 1) {
         $obj = $a_list[0];
         $obj->fetchAll(1);
         $content = new Template('../tpl/view_pserver.tpl');
         $content->set('obj', $obj);
         $content->set('what', 'physical server');
         goto screen;
       }
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('what', 'Physical Servers');
       $content->set('oc', 'PServer');
       $page['title'] .= 'Physical Servers';
     break;
     case 'vm':
       if (!isset($_POST['q']) || empty($_POST['q'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'Unknown option or not yet implemented');
	 goto screen;
       }
       $q = '%'.$_POST['q'].'%';
       $f = array();
       $s = array('ASC:name');
       $f['name'] = 'LIKE:'.$q;
       $a_list = VM::getAll(true, $f, $s);
       if (!count($a_list)) {
	 $content = new Template('../tpl/error.tpl');
         $content->set('error', 'No result found for your search...');
         goto screen;
       }
       if (count($a_list) == 1) {
	 $obj = $a_list[0];
         HTTP::redirect('/view/w/vm/i/'.$obj->id);
       }
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('what', 'VMs');
       $content->set('oc', 'VM');
       $page['title'] .= 'VMs';
     break;
     case 'server':
       if (!isset($_POST['q']) || empty($_POST['q'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'Unknown option or not yet implemented');
	 goto screen;
       }
       $q = '%'.$_POST['q'].'%';
       $f = array();
       $s = array('ASC:hostname');
       $f['hostname'] = 'LIKE:'.$q;
       $a_list = Server::getAll(true, $f, $s);
       if (!count($a_list)) {
	 $content = new Template('../tpl/error.tpl');
         $content->set('error', 'No result found for your search...');
         goto screen;
       }
       if (count($a_list) == 1) {
	 $obj = $a_list[0];
         HTTP::redirect('/view/w/server/i/'.$obj->id);
       }
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('what', 'Servers');
       $content->set('oc', 'Server');
       $page['title'] .= 'Servers';
     break;
     case 'cluster':
       if (!isset($_POST['q']) || empty($_POST['q'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'Unknown option or not yet implemented');
	 goto screen;
       }
       $q = '%'.$_POST['q'].'%';
       $f = array();
       $s = array('ASC:name');
       $f['name'] = 'LIKE:'.$q;
       $a_list = Cluster::getAll(true, $f, $s);
       if (!count($a_list)) {
	 $content = new Template('../tpl/error.tpl');
         $content->set('error', 'No result found for your search...');
         goto screen;
       }
       if (count($a_list) == 1) {
	 $obj = $a_list[0];
         HTTP::redirect('/view/w/cluster/i/'.$obj->id);
       }
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('what', 'Clusters');
       $content->set('oc', 'Cluster');
       $page['title'] .= 'Clusters';
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
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $head->set('page', $page);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
