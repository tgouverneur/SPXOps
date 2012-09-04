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

 /* Page setup */
 $page = array();
 $page['title'] = 'Home';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 $stats = array();
 $stats['nblogin'] = $m->count('list_login');
 $stats['nbsuser'] = $m->count('list_suser');
 $stats['nbswitch'] = $m->count('list_switch');
 $stats['nbos'] = $m->count('list_os');
 $stats['nbmodel'] = $m->count('list_model');
 $stats['nbsrv'] = $m->count('list_server');
 $stats['nbpsrv'] = $m->count('list_pserver');
 $stats['nbdisk'] = $m->count('list_disk');
 $stats['nbcl'] = $m->count('list_cluster');

 $a_job = Job::getAll(true, array(), array('DESC:t_add'), 0, 10);
 $a_act = Act::getAll(true, array(), array('DESC:t_add'), 0, 10);

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $head->set('page', $page);

 $foot = new Template("../tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $content = new Template("../tpl/home.tpl");
 $content->set('stats', $stats);
 $content->set('a_job', $a_job);
 $content->set('a_act', $a_act);

 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
