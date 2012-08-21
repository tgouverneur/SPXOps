<?php
/**
 * File used to store application settings
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package includes
 * @subpackage config
 * @category config
 */

$config['rootpath'] = '/srv/spxops';

/* UCC Database NG */
$config['mysql']['host'] = 'localhost';
$config['mysql']['user'] = 'spxops';
$config['mysql']['pass'] = 'jKGZPBHt8EaP4B7s';
$config['mysql']['port'] = 5601;
$config['mysql']['db'] = 'spxopsNG';

/* MySQL Debug mode, comment to disable. */
//$config['mysql']['DEBUG'] = '/tmp/mysqlcm.log';
$config['mysql']['DEBUG'] = FALSE;
$config['mysql']['ERRLOG'] = '/srv/spxops/logs/mysqlerr.log';
$config['mysql']['LOGNULL'] = FALSE;

$config['curl']['timeout'] = 10;
$config['curl']['proxy'] = '';

$config['webgui']['time'] = true;

/* Logging */
@require_once($config['rootpath'].'/libs/logger.obj.php');
Logger::logLevel(LLOG_ERR);
Logger::logLevel(LLOG_WARN);
Logger::logLevel(LLOG_INFO);

if ($config['webgui']['time']) {
 $start_time = microtime();
 $start_time = explode(' ',$start_time);
 $start_time = $start_time[1] + $start_time[0];
}

?>
