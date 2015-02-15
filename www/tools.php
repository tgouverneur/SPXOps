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

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $page = array();
 $page['title'] = 'Tool: ';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   HTTP::errWWW('You must be logged-in to access this page');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'stats':
       $what = 'Statistics';
       $page['title'] .= $what;
       $content = new Template('../tpl/statistics.tpl');
       $stats = array();
       $pool_filter = '/(slc|atl|ylw|ams)[0-9]{1,2}\.[bt]\.([0-9]{1,2}|[0-9]{1,2}-[0-9]{1,2})/';
       $a_pool = Pool::getAll();
       $stats['storage_total'] = 0;
       $stats['storage_used'] = 0;
       $stats['storage_free'] = 0;
       $stats['storage_nbp'] = 0;
       foreach($a_pool as $pool) {
	 if (preg_match($pool_filter, $pool->name)) {
	   $stats['storage_total'] += $pool->size;
	   $stats['storage_used'] += $pool->used;
	   $stats['storage_free'] += ($pool->size - $pool->used);
	   $stats['storage_nbp']++;
         }
       }
       $a_server = Server::getAll();
       $stats['hw_nrcpu'] = 0;
       $stats['hw_nrcore'] = 0;
       $stats['hw_memory'] = 0;
       foreach($a_server as $s) {
         $s->fetchData();
         $nrcpu = $s->data('hw:nrcpu');
         $nrcore = $s->data('hw:nrcore');
         $memory = $s->data('hw:memory');
	 if (!empty($nrcpu)) $stats['hw_nrcpu'] += $nrcpu;
	 if (!empty($nrcore)) $stats['hw_nrcore'] += $nrcore;
	 if (!empty($memory)) $stats['hw_memory'] += $memory;
       }
       $a_vm = VM::getAll();
       $stats['vm_nb'] = $m->count('list_vm', "WHERE `fk_server` != -1");
       $content->set('stats', $stats);

     break;
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
