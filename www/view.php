<?php
/**
 * View Objects
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage management
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
 require_once("../libs/utils.obj.php");

try {

 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   throw new ExitException('An error has occurred with the SQL Server and we were unable to process your request...');
 }
 $lm = LoginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $page = array();
 $page['title'] = 'Viewing ';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException('You must be logged-in to access this page');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'rights': // Special page to check user's right
       $what = 'user\'s rights';
       $content = new Template('../tpl/view_right.tpl');
       $page['title'] .= $what;
       $content->set('a_right', $lm->o_login->a_right);
     break;
     case 'pserver':
       if (!$lm->o_login->cRight('PHY', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Physical Server';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new PServer($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $content = new Template('../tpl/view_pserver.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
     break;
     case 'check':
       if (!$lm->o_login->cRight('CHK', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Check';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new Check($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchJT('a_sgroup');
       $content = new Template('../tpl/view_check.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('llist.js');
       $head->set('js', $js);
       $content->set('a_sgroup', SGroup::getAll(true, array(), array('ASC:name')));
     break;
     case 'sgroup':
       if (!$lm->o_login->cRight('SRVGRP', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Server Group';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new SGroup($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchJT('a_server');
       $obj->fetchJT('a_vm');
       $content = new Template('../tpl/view_sgroup.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $content->set('a_server', Server::getAll(true, array(), array('ASC:hostname')));
       $js = array('llist.js');
       $head->set('js', $js);
     break;
     case 'ugroup':
       if (!$lm->o_login->cRight('UGRP', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'User Group';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new UGroup($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchJT('a_login');
       $obj->fetchJT('a_right');
       $obj->fetchJT('a_alerttype');
       $obj->fetchJT('a_sgroup');
       $content = new Template('../tpl/view_ugroup.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $content->set('a_login', Login::getAll(true, array(), array('ASC:username')));
       $content->set('a_right', Right::getAll(true, array(), array('ASC:short')));
       $content->set('a_alerttype', AlertType::getAll(true, array(), array('ASC:short')));
       $content->set('a_sgroup', SGroup::getAll(true, array(), array('ASC:name')));
       $js = array('llist.js', 'rights.js', 'alerts.js');
       $head->set('js', $js);
     break;
     case 'suser':
       if (!$lm->o_login->cRight('CUSER', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Connect User';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new SUser($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $content = new Template('../tpl/view_suser.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('llist.js');
       $head->set('js', $js);
     break;
     case 'login':
       $self = false;
       if (!strcmp($_GET['i'], 'self')) {
         $_GET['i'] = $lm->o_login->id;
         $self = true;
       }
       if (!$lm->o_login->cRight('USR', R_VIEW) && !$self) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'User';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new Login($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchJT('a_ugroup');
       $content = new Template('../tpl/view_login.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $content->set('a_ugroup', UGroup::getAll(true, array(), array('ASC:name')));
       $content->set('a_act', Act::getAll(true, array('fk_login' => 'CST:'.$obj->id), array('DESC:t_add'),0, 10));
       $js = array('llist.js');
       $head->set('js', $js);
     break;
     case 'pool':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'ZFS Pool';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       } 
       $obj = new Pool($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchAll(1);
       $content = new Template('../tpl/view_pool.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('jobs.js', 'jquery.jqplot.min.js', 'jqplot.pieRenderer.min.js', 'jqplot.donutRenderer.min.js');
       $css = array('jquery.jqplot.min.css');
       $head->set('css', $css);
       $head->set('js', $js);
     break;
     case 'vm':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Virtual Machine';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       } 
       $obj = new VM($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchAll(1);
       $obj->getNets();
       $obj->getDisks();
       $content = new Template('../tpl/view_vm.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('jobs.js');
       $head->set('js', $js);
     break;
     case 'rrd':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'RRD';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       } 
       $obj = new RRD($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchAll(1);
       $content = new Template('../tpl/view_rrd.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('rrd.js', 'jquery.jqplot.min.js', 'jqplot.highlighter.min.js', 'jqplot.logAxisRenderer.min.js', 'jqplot.dateAxisRenderer.min.js');
       $css = array('jquery.jqplot.min.css');
       $head->set('js', $js);
       $head->set('css', $css);
     break;
     case 'server':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Server';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       } 
       $obj = new Server($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchAll(1);
       $content = new Template('../tpl/view_server.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('server.js', 'jobs.js', 'jquery.jqplot.min.js', 'jqplot.pieRenderer.min.js', 'jqplot.donutRenderer.min.js');
       $css = array('jquery.jqplot.min.css');
       $head->set('js', $js);
       $head->set('css', $css);
     break;
     case 'cluster':
       if (!$lm->o_login->cRight('CLUSTER', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Cluster';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new Cluster($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       $obj->fetchAll(1);
       $content = new Template('../tpl/view_cluster.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
       $js = array('jobs.js', 'jquery.jqplot.min.js', 'jqplot.pieRenderer.min.js');
       $css = array('jquery.jqplot.min.css');
       $head->set('css', $css);
       $head->set('js', $js);
       $foot->set('js', array('cluster.js'));
     break;
     case 'rjob':
       if (!$lm->o_login->cRight('RJOB', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Recurrent Job';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new RJob($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       try {
       $obj->fetchAll(1);
       } catch (Exception $e) {
	 echo '';
	 /* @TODO: maybe we should log theses exception to a special log to allow debugging... */
       }
       $content = new Template('../tpl/view_rjob.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
     break;

     case 'job':
       if (!$lm->o_login->cRight('JOB', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Job';
       if (!isset($_GET['i']) || empty($_GET['i'])) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You didn't provided the ID of the $what to view");
         goto screen;
       }
       $obj = new Job($_GET['i']);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "Unable to find the $what in database");
         goto screen;
       }
       try {
         $obj->fetchAll(1);
       } catch (Exception $e) {
	 echo '';
	 /* @TODO: maybe we should log theses exception to a special log to allow debugging... */
       }
       $content = new Template('../tpl/view_job.tpl');
       $page['title'] .= $what;
       $content->set('obj', $obj);
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

} catch (ExitException $e) {
     
    if ($e->type == 2) { 
        echo Utils::getJSONError($e->getMessage());
    } else {
        $h = Utils::getHTTPError($e->getMessage());
        echo $h->fetch();
    }    
     
} catch (Exception $e) {
    /* @TODO: LOG EXCEPTION */
    $h = Utils::getHTTPError('Unexpected Exception: ');
    echo $h->fetch();
}
?>
