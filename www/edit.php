<?php
/**
 * Edit Objects
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
 $page['title'] = 'Edit ';
 $page['action'] = 'Edit';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException('You must be logged-in to access this page');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'suser':
       if (!$lm->o_login->cRight('CUSER', R_EDIT)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $suid = $_GET['i'];
       } else {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', 'ID of SSH User is not provided');
	 goto screen;
       }
       $what = 'SSH User';
       $obj = new SUser($suid);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
	 $content->set('error', 'SSH User not found in the database');
	 goto screen;
       }
       $content = new Template('../tpl/form_suser.tpl');
       $content->set('obj', $obj);
       $content->set('edit', true);
       $content->set('page', $page);
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Edit button */
         $fields = array('description', 'pubkey', 'username', 'password');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if (isset($_POST[$field])) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid(false);
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->update();
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "SSH User $obj has been updated");
         goto screen;
       }
     break;
     case 'cluster':
       if (!$lm->o_login->cRight('CLUSTER', R_EDIT)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Cluster';
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $cuid = $_GET['i'];
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'ID of Cluster is not provided');
         goto screen;
       }
       $obj = new Cluster($cuid);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'Cluster not found in the database');
         goto screen;
       }
       $obj->fetchRL('a_server');
       $obj->detectOsFromNodes();
       $content = new Template('../tpl/form_cluster.tpl');
       $a_os = OS::getAll(true, array(), array('ASC:name'));
       $content->set('oses', $a_os);
       $js = array('cluster.js');
       $head->set('js', $js);
       $page['title'] .= $what;
       $content->set('edit', true);
       $content->set('page', $page);
       if (isset($obj->fk_os) && is_numeric($obj->fk_os) && $obj->fk_os > 0) {
         $a_server = Server::getAll(true, array('fk_os' => 'CST:'.$obj->fk_os), array('ASC:hostname'));
         $content->set('a_server', $a_server);
	 /* sort the a_server array of $obj to have index as server id */
         $a = $obj->a_server;
	 $obj->a_server = array();
	 foreach($a as $o) {
	   $obj->a_server[$o->id] = $o;
 	 }
       }
       if (isset($_POST['submit'])) { /* clicked on the Edit button */
         $objold = $obj;
         $obj = new Cluster();
         $fields = array('name', 'description', 'f_upd', 'a_server', 'fk_os');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if (isset($_POST[$field])) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid(false, $objold);
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $objold);
           goto screen;
         }
         $objold->update();
	 foreach($obj->a_server as $s) {
	   $s->fk_cluster = $objold->id;
	   $s->update();
	 }
	 foreach($objold->a_server as $s) {
	   if (!isset($obj->a_server[$s->id])) {
	     $s->fk_cluster = -1;
	     $s->update();
	   }
	 }
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Cluster $obj has been updated inside database");
         goto screen;
       }
       $content->set('obj', $obj);
     break;
     case 'server':
       if (!$lm->o_login->cRight('SRV', R_EDIT)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Server';
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $suid = $_GET['i'];
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'ID of Server is not provided');
         goto screen;
       }
       $what = 'Server';
       $obj = new Server($suid);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'Server not found in the database');
         goto screen;
       }
       $content = new Template('../tpl/form_server.tpl');
       $content->set('obj', $obj);
       $content->set('edit', true);
       $content->set('page', $page);
       $a_suser = SUser::getAll(true, array(), array('ASC:username'));
       $a_pserver = PServer::getAll(true, array(), array('ASC:name'));
       $content->set('susers', $a_suser);
       $content->set('pservers', $a_pserver);
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Edit button */
         $fields = array('description', 'fk_pserver', 'fk_suser', 'f_rce', 'f_upd');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid(false);
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
	 if ($obj->fk_pserver == -2) { /* should be added */
	   $ps = new PServer();
	   $ps->name = $obj->hostname;
	   $ps->insert();
	   $obj->fk_pserver = $ps->id;
	 }
         $obj->update();
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Server $obj has been updated to database");
         goto screen;
       }
     break;
     case 'check':
       if (!$lm->o_login->cRight('CHK', R_EDIT)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Check';
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $suid = $_GET['i'];
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'ID of Check is not provided');
         goto screen;
       }
       $what = 'Check';
       $obj = new Check($suid);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'Check not found in the database');
         goto screen;
       }
       $content = new Template('../tpl/form_check.tpl');
       $content->set('obj', $obj);
       $content->set('edit', true);
       $content->set('page', $page);
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Edit button */
         $fields = array('description', 'm_error', 'm_warn', 'f_noalerts', 'f_vm', 'f_root', 'frequency', 'lua');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid(false);
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->update();
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Check $obj has been updated to database");
         goto screen;
       }
     break;
     case 'sgroup':
       if (!$lm->o_login->cRight('SRVGRP', R_EDIT)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'Server Group';
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $suid = $_GET['i'];
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'ID of Server Group is not provided');
         goto screen;
       }
       $what = 'Server Group';
       $obj = new SGroup($suid);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'Server Group not found in the database');
         goto screen;
       }
       $content = new Template('../tpl/form_sgroup.tpl');
       $content->set('obj', $obj);
       $content->set('edit', true);
       $content->set('page', $page);
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Edit button */
         $fields = array('description');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid(false);
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->update();
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "Server Group $obj has been updated to database");
         goto screen;
       }
     break;
     case 'ugroup':
       if (!$lm->o_login->cRight('UGRP', R_EDIT)) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       $what = 'User Group';
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $suid = $_GET['i'];
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'ID of User Group is not provided');
         goto screen;
       }
       $what = 'User Group';
       $obj = new UGroup($suid);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'User Group not found in the database');
         goto screen;
       }
       $content = new Template('../tpl/form_ugroup.tpl');
       $content->set('obj', $obj);
       $content->set('edit', true);
       $content->set('page', $page);
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Edit button */
         $fields = array('description');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
         }
         $errors = $obj->valid(false);
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }
         $obj->update();
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "User Group $obj has been updated to database");
         goto screen;
       }
     break;
     case 'login':
       $self = false;
       if (!strcmp($_GET['i'], 'self') || $_GET['i'] == $lm->o_login->id) {
         $_GET['i'] = $lm->o_login->id;
         $self = true;
       }
       if (!$lm->o_login->cRight('USR', R_EDIT) && !$self) {
         throw new ExitException('Access Denied, please check your access rights!');
       }
       if (isset($_GET['i']) && !empty($_GET['i'])) {
         $suid = $_GET['i'];
       } else {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'ID of User is not provided');
         goto screen;
       }
       $what = 'User';
       $obj = new Login($suid);
       if ($obj->fetchFromId()) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', 'User not found in the database');
         goto screen;
       }
       $what = 'User';
       $content = new Template('../tpl/form_user.tpl');
       $content->set('obj', $obj);
       $content->set('edit', true);
       $content->set('page', $page);
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Edit button */
         $fields = array('fullname', 'email', 'password', 'password_c', 'f_noalerts', 'f_admin', 'f_api');
         foreach($fields as $field) {
           if (!strncmp($field, 'f_', 2)) { // should be a checkbox
             if (isset($_POST[$field])) {
               $obj->{$field} = 1;
             } else {
               $obj->{$field} = 0;
             }
           } else {
             if ($_POST[$field]) {
               $obj->{$field} = $_POST[$field];
             }
           }
	     }

         $errors = $obj->valid(false);
         if ($errors) {
           $content->set('error', $errors);
           $content->set('obj', $obj);
           goto screen;
         }

         $lm = LoginCM::getInstance();
         if (!$obj->f_active && isset($_POST['f_active']) && !$lm->o_login->f_admin) {
             $content->set('error', 'You cannot set account as active as you aren\'t administrator yourself.');
             $content->set('obj', $obj);
             $obj->f_active = 0;
             goto screen;
         } else if (!$obj->f_active && isset($_POST['f_active']) && $lm->o_login->f_admin) {
             $obj->f_active = 1;
         }

         /* Must crypt the password */
         if (!empty($obj->password) && !empty($obj->password_c)) $obj->bcrypt($obj->password); // If password_c is empty then password has not been modified
         $obj->update();
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', "User $obj has been updated to database");
         goto screen;
       }
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

} catch (ExitException $e) {
     
    if ($e->type == 2) { 
        echo Utils::getJSONError($e->getMessage());
    } else {
        $h = Utils::getHTTPError($e->getMessage());
        echo $h->fetch();
    }    
     
} catch (Exception $e) {
    /* @TODO: LOG EXCEPTION */
    $h = Utils::getHTTPError('Unexpected Exception');
    echo $h->fetch();
}
?>
