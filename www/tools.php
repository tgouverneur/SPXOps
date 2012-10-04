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
 $page['title'] = 'Tool: ';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'cdp':
       $what = 'CDP Packet Parser';
       $page['title'] .= $what;
       $content = new Template('../tpl/form_cdp.tpl');
       if (isset($_POST['type']) && isset($_POST['packet'])) {
         if (empty($_POST['type']) || empty($_POST['packet'])) {
           $content->set('error', 'Missing field');
	   goto screen;
	 }
         if ($_POST['type'] == 1) {
  	   $cdpp = new CDPPacket('tcpdump', $_POST['packet']);
	 } else if ($_POST['type'] == 2) {
  	   $cdpp = new CDPPacket('snoop', $_POST['packet']);
	 } else {
           $content->set('error', 'Unknown type');
           goto screen;
	 }
         $cdpp->treat();
         $content = new Template('../tpl/view_cdp.tpl');
	 $content->set('pkt', $cdpp);
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
   $content->set('error', "I don't know what tool to use...");
 }

screen:
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $head->set('page', $page);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
