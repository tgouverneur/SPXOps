<?php
/**
 * Search
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
 $page['title'] = 'List of ';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException(null, EXIT_LOGIN);
 }

 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'pserver':
       if (!$lm->o_login->cRight('PHY', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
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
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
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
         throw new ExitException('none', 3, '/view/w/vm/i/'.$obj->id);
       }
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('what', 'VMs');
       $content->set('oc', 'VM');
       $page['title'] .= 'VMs';
     break;
     case 'server':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
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
         throw new ExitException('none', 3, '/view/w/server/i/'.$obj->id);
       }
       $content = new Template('../tpl/list.tpl');
       $content->set('a_list', $a_list);
       $content->set('canView', true);
       $content->set('what', 'Servers');
       $content->set('oc', 'Server');
       $page['title'] .= 'Servers';
     break;
     case 'cluster':
       if (!$lm->o_login->cRight('CLUSTER', R_VIEW)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
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
         throw new ExitException('none', 3, '/view/w/cluster/i/'.$obj->id);
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

} catch (ExitException $e) {
     
    if ($e->type == 2) { 
        echo Utils::getJSONError($e->getMessage());
    } else if ($e->type == 1) {
        $h = Utils::getHTTPError($e->getMessage());
        echo $h->fetch();
    } else if ($e->type == EXIT_LOGIN) { /* login needed */
        LoginCM::requestLogin();
    } else if ($e->type == 3) {
        HTTP::redirect($e->dest);
    }
     
} catch (Exception $e) {
    /* @TODO: LOG EXCEPTION */
    $h = Utils::getHTTPError('Unexpected Exception');
    echo $h->fetch();
}
?>
