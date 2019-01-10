<?php
/**
 * Installer Page
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2019, Gouverneur Thomas
 * @version 1.0
 * @package installer
 * @category www
 * @subpackage installer
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
 require_once("../libs/utils.obj.php");

try {

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $content = new Template("../tpl/home.tpl");

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
    $h = Utils::getHTTPError('Unexpected Exception');
    echo $h->fetch();
}
?>
