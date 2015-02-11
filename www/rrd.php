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

 if (!$h->isAjax()) {
   /* Page setup */
   $page = array();
   $page['title'] = 'Error';
   if ($lm->o_login) $page['login'] = &$lm->o_login;

   $index = new Template("../tpl/index.tpl");
   $head = new Template("../tpl/head.tpl");
   $head->set('page', $page);
   $foot = new Template("../tpl/foot.tpl");
   $foot->set("start_time", $start_time);

   $content = new Template("../tpl/error.tpl");
   $content->set('error', "The page you requested cannot be called as-is...");

   $index->set('head', $head);
   $index->set('content', $content);
   $index->set('foot', $foot);
   echo $index->fetch();
   exit(0);
 }

 if (!$lm->o_login) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must be logged-in';
   goto screen;
 }
 $lm->o_login->fetchRights();

 $i = null;

 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$i) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 if (!isset($_POST['start']) ||
     !isset($_POST['what']) ||
     !isset($_POST['n'])) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 $start = $_POST['start'];
 $what = $_POST['what'];
 $cid = 1;
 $mets = array();
 $n = $_POST['n'];
 if (isset($_POST['cid'])) $cid = $_POST['cid'];
 if (isset($_POST['mets'])) $mets = $_POST['mets'];

 $ret['cid'] = $cid;

 if (!is_numeric($n)) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Error in the provided arguments';
   goto screen;
 }

 if (!$lm->o_login->cRight('SRV', R_VIEW)) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You don\'t have the rights to run this action';
   goto screen;
 }

 if (!strcmp($i, 'group')) {
   if (!isset($mets) || !count($mets)) {
     $ret['rc'] = 1;
     $ret['msg'] = 'There are no metric given, no data to graph';
   }
   /* simplify met list by aggregating per rrd */
   $a_m = array();
   foreach ($mets as $met) {
     $rid = $met[1];
     $mn = $met[2];
     if (!isset($a_m[$rid])) { 
       $a_m[$rid] = $mn;
     } else {
       $a_m[$rid] .= ','.$mn;
     }
   }
   $res = array();
   $res['values'] = array();
   $res['labels'] = array();
   foreach ($a_m as $rid => $what) {
     $obj = new RRD($rid);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find RRD provided inside the database';
       goto screen;
     }
     try {

       $r = $obj->getData($start, $what, $n);
       foreach($r['values'] as $e) {
         $res['values'][] = $e;
       }
       foreach($r['labels'] as $e) {
         foreach ($e as $l) {
           $res['labels'][] = array('label' => $obj.'/'.$l);
         }
         //$res['labels'][] = $e;
       }

     } catch (SPXException $e) {

       $ret['rc'] = 1;
       $ret['res'] = $e.toString();
       goto screen;
     }
   }
   $ret['res'] = $res;
   $ret['rc'] = 0;

 } else {

  $obj = new RRD($i);
  if ($obj->fetchFromId()) {
    $ret['rc'] = 1;
    $ret['msg'] = 'Cannot find RRD provided inside the database';
    goto screen;
  }

  try {
    $ret['res'] = $obj->getData($start, $what, $n);
    $ret['rc'] = 0;
  } catch (SPXException $e) {

    $ret['rc'] = 1;
    $ret['res'] = $e.toString();
    goto screen;
  }
 }

screen:
 echo json_encode($ret);

?>
