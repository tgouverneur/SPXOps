<?php
/**
 * RPC
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage JSON
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

 if (!$h->isAjax()) {
     throw new ExitException('The page you requested cannot be called as-is...', 1);
 }

 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
     throw new ExitException('You must be logged-in', 2);
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'saveslr':
        if (!isset($_POST['name']) || empty($_POST['name'])) {
            throw new ExitException('Missing argument');
       }
        if (!isset($_POST['mets']) || empty($_POST['mets'])) { 
            throw new ExitException('Missing argument');
       }
       $name = $_POST['name'];
       $mets = $_POST['mets'];
       $slr = new SLR();
       $slr->name = $name;
       if (!$slr->fetchFromField('name')) { 
            throw new ExitException('Name already taken');
       }
       $slr->definition = serialize($mets);
       $slr->insert();
       $ret = array('success');
       header('Content-Type: application/json');
       echo json_encode($ret);
    break;
     case 'lslr':
       $a_s = SLR::getAll(true, array(), array('name'));
       foreach($a_s as $s) $s->getArray();
       header('Content-Type: application/json');
       echo json_encode($a_s);
     break;
     case 'lserver':
       $o = null;
       if (isset($_GET['o']) && !empty($_GET['o'])) {
         $o = $_GET['o'];
       }
       $a_s = Server::getAll(true, array(), array('hostname'));
       if ($o && !strcmp($o, 'rrd')) {
         $ret = array();
         $a_rrd = RRD::getAll(true, array(), array('fk_server'));
         foreach($a_s as $s) {
           foreach($a_rrd as $r) {
             if ($r->fk_server == $s->id) { array_push($ret, $s); break; }
	   }
         }
         $a_s = $ret;
       }
       header('Content-Type: application/json');
       echo json_encode($a_s);
     break;
     case 'lmet':
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
            throw new ExitException('Missing argument');
       }
       $id = $_GET['i'];
       $rrd = new RRD($id);
       if ($rrd->fetchFromId()) {
            throw new ExitException('Wrong argument');
       }
       $val = $rrd->getWhat('all');
       $ret = array();
       $i = 0;
       foreach($val as $k => $v) {
         $ret[$i]['name'] = $k;
         $ret[$i]['value'] = $v;
         $i++;
       }
       header('Content-Type: application/json');
       echo json_encode($ret);
     break;
     case 'lrrd':
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
            throw new ExitException('Missing argument');
       }
       $id = $_GET['i'];
       $a_rrd = RRD::getAll(true, array('fk_server' => 'CST:'.$id), array('type'));
       header('Content-Type: application/json');
       echo json_encode($a_rrd);
     break;
     case 'slr':
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
            throw new ExitException('Missing argument');
       }
       $id = $_GET['i'];
       $slr = new SLR($id);
       if ($slr->fetchFromId()) {
            throw new ExitException('SLR Not found in DB');
       }
       $slr->getArray();
       header('Content-Type: application/json');
       echo json_encode($slr);
     break;
     case 'vm':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
            throw new ExitException('Not authorized');
       }
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
            throw new ExitException('Missing argument');
       }
       $id = $_GET['i'];
       $s = new VM();
       if ($s->fetchFromId()) {
           throw new ExitException('Not found');
       }
       header('Content-Type: application/json');
       echo json_encode($s->jsonSerialize(false));
     break;
     case 'sname':
       if (!$lm->o_login->cRight('SRV', R_VIEW)) {
            throw new ExitException('Not authorized');
       }
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
            throw new ExitException('Missing argument');
       }
       $id = $_GET['i'];
       $s = new Server();
       if ($s->fetchFromId()) {
           throw new ExitException('Not found');
       }
       header('Content-Type: application/json');
       echo json_encode($s->jsonSerialize());
     break;
     case 'server':
       if (!isset($_GET['s']) || empty($_GET['s'])) {
            throw new ExitException('Missing argument');
       }
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
            throw new ExitException('Missing argument');
       }
       $id = $_GET['i'];
       $a_server = Server::getAll(true, array('fk_os' => 'CST:'.$id), array('hostname'));
       header('Content-Type: application/json');
       echo json_encode($a_server);
     break;
     case 'cr':
       if (!$lm->o_login->cRight('CHK', R_VIEW)) {
            throw new ExitException('Not authorized');
       }
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
            throw new ExitException('Missing argument');
       }
       $id = $_GET['i'];
       $cr = new Result($id);
       if ($cr->fetchFromId()) {
            throw new ExitException('Cannot fetch result');
       }
       $ret = array();
       $ret['id'] = $cr->id;
       $ret['message'] = $cr->message;
       $ret['details'] = $cr->details;
       header('Content-Type: application/json');
       echo json_encode($ret);
     break;
     case 'currentJobs':
       if (!$lm->o_login->cRight('JOB', R_VIEW)) {
           throw ExitException('Not Authorized');
       }
       if (!isset($_GET['class']) || empty($_GET['class'])) {
            throw new ExitException('Missing argument');
       }
       $jc = $_GET['class'];
       $filter = array('class' => $jc);
       if (isset($_GET['fct'])) {
           $filter['fct'] = $_GET['fct'];
       }
       $a_job = Job::getAll(true, $filter, array('DESC:t_add'));
       $ret = array();
       foreach($a_job as $job) {
           if ($job->state != S_RUN &&
               $job->state != S_NEW)
           {
               continue;
           }
           $ret[] = $job->id;
       }
       header('Content-Type: application/json');
       echo json_encode($ret);

     break;
     case 'job':
       if (!isset($_GET['i']) || empty($_GET['i']) || !is_numeric($_GET['i'])) {
            throw new ExitException('Missing argument');
       }
       $id = $_GET['i'];
       $job = new Job($id);
       if ($job->fetchFromId()) {
            throw new ExitException('Cannot fetch Job');
       }
       if (!$lm->o_login->cRight('JOB', R_VIEW) && $job->fk_login != $lm->o_login->id) {
            throw new ExitException('Not authorized');
       }
       try {
         $job->fetchAll(1);
       } catch (Exception $e) {
         // do nothing!
       }
       $ret = array();
       $ret['id'] = $job->id;
       $ret['state'] = $job->stateStr();
       $ret['fct'] = $job->fct;
       $ret['pc_progress'] = $job->pc_progress;
       $ret['elapsed'] = -1;
       $ret['pid'] = '';
       $ret['log'] = '';
       $ret['start'] = '';
       $ret['add'] = '';
       $ret['upd'] = '';
       $ret['stop'] = '';
       if ($job->o_pid) $ret['pid'] = $job->o_pid->pid;
       if ($job->t_start > 0) $ret['start'] = date('d-m-Y H:i:s', $job->t_start);
       if ($job->t_stop > 0) $ret['stop'] = date('d-m-Y H:i:s', $job->t_stop);
       if ($job->t_add > 0) $ret['add'] = date('d-m-Y H:i:s', $job->t_add);
       if ($job->t_upd > 0) $ret['upd'] = date('d-m-Y H:i:s', $job->t_upd);
       if ($job->t_start > 0) $ret['elapsed'] = (time() - $job->t_start);
       if ($job->o_log) $ret['log'] = $job->o_log->log;
       header('Content-Type: application/json');
       echo json_encode($ret);
     break;
     default:
       throw new ExitException('Unknown option or not implemented', 2);
     break;
   }
 }

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
