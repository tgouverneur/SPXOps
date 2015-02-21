<?php
/**
 * Main Page
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage index
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

 Setting::fetchAll();

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
 $stats['nbsgroup'] = $m->count('list_sgroup');
 $stats['nbugroup'] = $m->count('list_ugroup');
 $stats['nbcheck'] = $m->count('list_check');
 $stats['nbpsrv'] = $m->count('list_pserver');
 $stats['nbdisk'] = $m->count('list_disk');
 $stats['nbcl'] = $m->count('list_cluster');
 $stats['nbzone'] = $m->count('list_zone');
 $stats['nbvm'] = $m->count('list_vm');
 $stats['nbpool'] = $m->count('list_pool');

 $a_job = Job::getAll(true, array(), array('DESC:t_add'), 0, 10);
 $a_act = Act::getAll(true, array(), array('DESC:t_add'), 0, 10);
 $a_result = Result::getAll(true, array(), array('DESC:t_upd'), 0, 10);

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $head->set('page', $page);

 $foot = new Template("../tpl/foot.tpl");
 $content = new Template("../tpl/home.tpl");
 $content->set('stats', $stats);
 $content->set('a_job', $a_job);
 $content->set('a_act', $a_act);
 $content->set('a_result', $a_result);

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
