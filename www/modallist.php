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

 $page = array();
 $page['title'] = 'List of ';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'patches':
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
     case 'projects':
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
