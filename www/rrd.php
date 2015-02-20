<?php
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

 if (!$h->isAjax()) {
     throw new ExitException('The page you requested cannot be called as-is...', 1);
 }

 if (!$lm->o_login) {
     throw new ExitException('You must be logged-in', 2);
 }
 $lm->o_login->fetchRights();

 $i = null;

 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$i) {
     throw new ExitException('You must provide proper arguments', 2);
 }

 if (!isset($_POST['start']) ||
     !isset($_POST['what']) ||
     !isset($_POST['n'])) {
         throw new ExitException('You must provide proper arguments', 2);
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
     throw new ExitException('Error in the provided arguments');
 }

 if (!$lm->o_login->cRight('SRV', R_VIEW)) {
     throw new ExitException('You don\'t have the rights to run this action');
 }

 if (!strcmp($i, 'group')) {
   if (!isset($mets) || !count($mets)) {
     throw new ExitException('There are no metric given, no data to graph');
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
       throw new ExitException('Cannot find RRD provided inside the database');
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
       }

     } catch (SPXException $e) {
       throw new ExitException($e.toString());
     }
   }
   $ret['res'] = $res;
   $ret['rc'] = 0;

 } else {

  $obj = new RRD($i);
  if ($obj->fetchFromId()) {
    throw new ExitException('Cannot find RRD Provided inside the database');
  }

  try {
    $ret['res'] = $obj->getData($start, $what, $n);
    $ret['rc'] = 0;
  } catch (SPXException $e) {
    throw new ExitException($e.toString());
  }
 }

 echo json_encode($ret);

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
