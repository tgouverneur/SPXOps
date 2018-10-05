<?php
/**
 * SlackMSG
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2018, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @subpackage job
 * @category classes
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */


class SlackMSG {
    public static function sendMessage($msg) {
         if (!Config::$slackwhook) {
             return;
         }
         $a_arg = array();
         $a_arg['text'] = $msg;
         $data = json_encode($a_arg);
         $c = curl_init(Config::$slackwhook);
         curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
         curl_setopt($c, CURLOPT_HEADER, 0);
         curl_setopt($c, CURLOPT_TIMEOUT, 10);
         curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
         curl_setopt($c, CURLOPT_POSTFIELDS, $data);
         curl_setopt($c, CURLOPT_HTTPHEADER, array(
             'Content-Type: application/json',
             'Content-Length: ' . strlen($data))
         );
         $data = curl_exec($c);
         curl_close($c);
         $a_ret = json_decode($data, true);
         print_r($a_ret);
    }
}

?>
