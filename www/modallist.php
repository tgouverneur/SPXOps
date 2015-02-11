<?php
 require_once("../libs/utils.obj.php");


 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = LoginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $page = array();
 $page['title'] = 'List of ';

 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   HTTP::errWWW('You must be logged-in to access this page');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'logs':
       if (!isset($_GET['o']) || empty($_GET['o'])) {
         $content = new Template('../tpl/modalerror.tpl');
	 $content->set('error', 'No class specified');
         goto screen;
       }
       $o_name = $_GET['o'];
       if (!class_exists($o_name) ||
           !method_exists($o_name, 'fetchLogs')) {
         $content = new Template('../tpl/modalerror.tpl');
	 $content->set('error', 'This kind of object doesn\'t support Logs');
         goto screen;
       }
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'ID Not specified');
         goto screen;
       }
       $obj = new $o_name($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Object not found inside database');
         goto screen;
       }
       $obj->fetchLogs();
       $content = new Template('../tpl/modallist.tpl');
       $content->set('a_list', $obj->a_log);
       $content->set('oc', 'Log');
     break;
     case 'rs':
       if (!$lm->o_login->cRight('CLUSTER', R_VIEW)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Resource group ID not provided');
         goto screen;
       }
       $rg = new CLRg($_GET['i']);
       if ($rg->fetchFromId()) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Resource group ID not found');
         goto screen;
       }
       $rg->fetchRL('a_clrs');
       $content = new Template('../tpl/modallist.tpl');
       $content->set('a_list', $rg->a_clrs);
       $content->set('oc', 'CLRs');
     break;
     case 'patches':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not provided');
	 goto screen;
       }
       $s = new Server($_GET['i']);
       if ($s->fetchFromId()) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not found');
	 goto screen;
       }
       $s->fetchRL('a_patch');
       $content = new Template('../tpl/modallist.tpl');
       $content->set('a_list', $s->a_patch);
       $content->set('oc', 'Patch');
     break;
     case 'results':
       if (!$lm->o_login->cRight('CHKBOARD', R_VIEW)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Check ID not provided');
         goto screen;
       }
       $a_list = Result::getAll(true, array('fk_check' => $_GET['i']), array('DESC:t_upd', 'DESC:t_add'));
       $content = new Template('../tpl/modallist.tpl');
       $content->set('a_list', $a_list);
       $content->set('oc', 'Result');
     break;
     case 'sresults':
       if (!$lm->o_login->cRight('CHKBOARD', R_VIEW)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not provided');
         goto screen;
       }
       $a_list = Result::getAll(true, array('fk_server' => $_GET['i']), array('DESC:t_upd', 'DESC:t_add'));
       $content = new Template('../tpl/modallist.tpl');
       $content->set('a_list', $a_list);
       $content->set('oc', 'Result');
       $content->set('notStripped', true);
     break;
     case 'projects':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not provided');
         goto screen;
       }
       $s = new Server($_GET['i']);
       if ($s->fetchFromId()) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not found');
         goto screen;
       }
       $s->fetchRL('a_prj');
       $content = new Template('../tpl/modallist.tpl');
       $content->set('a_list', $s->a_prj);
       $content->set('oc', 'Prj');
     break;
     case 'packages':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not provided');
         goto screen;
       }
       $s = new Server($_GET['i']);
       if ($s->fetchFromId()) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not found');
         goto screen;
       }
       $s->fetchRL('a_pkg');
       $content = new Template('../tpl/modallist.tpl');
       $content->set('a_list', $s->a_pkg);
       $content->set('oc', 'Pkg');
     break;
     case 'disks':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         HTTP::errWWW('Access Denied, please check your access rights!');
       }
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not provided');
         goto screen;
       }
       $s = new Server($_GET['i']);
       if ($s->fetchFromId()) {
         $content = new Template('../tpl/modalerror.tpl');
         $content->set('error', 'Server ID not found');
         goto screen;
       }
       $s->fetchRL('a_disk');
       $content = new Template('../tpl/modallist.tpl');
       $content->set('a_list', $s->a_disk);
       $content->set('oc', 'Disk');
       $content->set('info', 'EMC Disks Lunid correspond to the EMC Device ID.');
     break;
     default:
       $content = new Template('../tpl/modalerror.tpl');
       $content->set('error', 'Unknown option or not yet implemented');
     break;
   }
 } else {
   $content = new Template('../tpl/modalerror.tpl');
   $content->set('error', "I don't know what to list...");
 }

screen:
 echo $content->fetch();

?>
