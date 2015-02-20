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
 if (!$lm->o_login->cRight('JOB', R_ADD)) {
     throw new ExitException('You don\'t have the rights to ack check', 2);
 }

 $c = $f = $a = null;

 if (isset($_GET['c']) && !empty($_GET['c'])) {
   $c = $_GET['c'];
 }
 if (isset($_GET['f']) && !empty($_GET['f'])) {
   $f = $_GET['f'];
 }
 if (isset($_GET['a']) && !empty($_GET['a'])) {
   $a = $_GET['a'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$c || !$f || !$a) {
     throw new ExitException('You must provide proper arguments', 2);
 }

 switch ($c) {
   case 'Check':
     if ($f == 'jobServer') {
       $s = new Server($a);
       if ($s->fetchFromId()) {
         throw new ExitException('Server specified not found in database', 2);
       }
       $j = new Job();
       $j->class = $c;
       $j->fct = $f;
       $j->arg = $a;
       $j->state = S_NEW;
       $j->fk_login = $lm->o_login->id;
       $j->insert();
       Act::add("Requested an update of the server $s", $lm->o_login);
       $ret['rc'] = 0;
       $ret['msg'] = "Job to check server $s has been succesfully added to the queue...";
     }
   break;
   case 'Update':
     if ($f == 'jobServer') {
       $s = new Server($a);
       if ($s->fetchFromId()) {
         throw new ExitException('Server specified not found in database', 2);
       }
       $j = new Job();
       $j->class = $c;
       $j->fct = $f;
       $j->arg = $a;
       $j->state = S_NEW;
       $j->fk_login = $lm->o_login->id;
       $j->insert();
       Act::add("Requested an update of the server $s", $lm->o_login);
       $ret['rc'] = 0;
       $ret['msg'] = "Job to update server $s has been succesfully added to the queue...";
     } else if ($f == 'jobCluster') {
       $oc = new Cluster($a);
       if ($oc->fetchFromId()) {
         throw new ExitException('Cluster specified not found in database', 2);
       }
       $j = new Job();
       $j->class = $c;
       $j->fct = $f;
       $j->arg = $a;
       $j->state = S_NEW;
       $j->fk_login = $lm->o_login->id;
       $j->insert();
       Act::add("Requested an update of the cluster $oc", $lm->o_login);
       $ret['rc'] = 0;
       $ret['msg'] = "Job to update cluster $oc has been succesfully added to the queue...";
     } 
   break;
   default:
     throw new ExitException('Unknown class provided', 2);
   break;
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
