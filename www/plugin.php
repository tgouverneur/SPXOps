<?php
/**
 * Plugin interface
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage plugins
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
 $page['title'] = 'Plugins';

 if ($lm->o_login) {
    $page['login'] = &$lm->o_login;
    $lm->o_login->fetchRights();
 }

 $js = array();
 $css = array();
 $a_link = null;
 $head_code = null;

 if (isset($_GET['p']) && !empty($_GET['p']) &&
     isset($_GET['w']) && !empty($_GET['w'])) {

       $p = $_GET['p'];
       $w = $_GET['w'];

       $wa = Plugin::getWebAction($p, $w);
       if (!$wa) {
         throw new ExitException('The Plugin or Action you requested is not registered');
       }

       if ($wa->n_right && $wa->n_level) {
       
           if (!$lm->o_login) {
               throw new ExitException(null, EXIT_LOGIN);
           }

           if ($wa->n_right) { /* Preliminary right check */
             if (!$lm->o_login->cRight($wa->n_right, $wa->n_level)) {
               throw new ExitException('Access Denied, please check your access rights!');
             }
           }
       }
       $content = null;
       $wa->call($wa); /* supposed to fill $content */

       if (!$content) {
         throw new ExitException('Something wrong happened in plugin '.$wa->o_plugin->name, $wa->otype + 1);
       }

       $page['title'] .= $wa->desc;

 } else {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "I don't know what you're talking about...");
 }

 if (isset($wa) && $wa->otype == 1 && !($content instanceof Template)) {

     header('Content-Type: application/json');
     echo json_encode($content);

 } else {
     
     $head->set("js", $js);
     $head->set("css", $css);
     $head->set('page', $page);
     $head->set('head_code', $head_code);
     if (isset($a_link)) $foot->set('a_link', $a_link);
     $index->set('head', $head);
     $index->set('content', $content);
     $index->set('foot', $foot);

     echo $index->fetch();
 }

} catch (ExitException $e) {
     
    if ($e->type == EXIT_JSON) { 
        echo Utils::getJSONError($e->getMessage());
    } else if ($e->type == EXIT_HTTP) {
        $h = Utils::getHTTPError($e->getMessage());
        echo $h->fetch();
    } else if ($e->type == EXIT_REDIR) {
        HTTP::redirect($e->dest);
    } else if ($e->type == EXIT_DOWN) {
        foreach($e->options as $o) {
            header($o);
        }
        header('Content-Type: '.$e->dest);
        echo $e->getMessage();
    } else if ($e->type == EXIT_LDOWN) {
        foreach($e->options as $o) {
            header($o);
        }
        header('Content-Type: '.$e->dest);
        @ob_end_flush();
        flush();
        if ($e->fp) {
            while(!feof($e->fp)) {
                echo fread($e->fp, 128 * 1024);
                @ob_end_flush();
                flush();
            }
            fclose($e->fp);
        }
    } else if ($e->type == EXIT_LOGIN) { /* login needed */
        LoginCM::requestLogin();
    }
     
} catch (Exception $e) {
    /* @TODO: LOG EXCEPTION */
    $h = Utils::getHTTPError('Unexpected Exception');
    //print $e;
    echo $h->fetch();
}

?>
