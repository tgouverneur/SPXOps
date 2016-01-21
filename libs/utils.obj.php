<?php

class Utils
{
    public static function formatSeconds($i) {
        if ($i == 0) {
            return 'now';
        }
        $ret = '';
        if ($i >= 3600) {
            $h = floor($i / 3600);
            $ret .= $h.'h';
            $i -= 3600*$h;
        }
        if ($i >= 60) {
            $h = floor($i / 60);
            $ret .= $h.'m';
            $i -= 60*$h;
        }
        if ($i > 0) {
            $ret .= $i.'s';
        }
        return $ret;
    }

    public static function getHTTPError($msg) {
        $page = array();
        $page['title'] = 'Error';
        $lm = LoginCM::getInstance();
        if ($lm->o_login) $page['login'] = &$lm->o_login;

        $index = new Template("../tpl/index.tpl");
        $head = new Template("../tpl/head.tpl");
        $head->set('page', $page);
        $foot = new Template("../tpl/foot.tpl");
                 
        $content = new Template("../tpl/error.tpl");
        $content->set('error', $msg);
                     
        $index->set('head', $head);
        $index->set('content', $content);
        $index->set('foot', $foot);
        return $index;
    }

    public static function getJSONError($msg) {
        return json_encode(array('rc' => 1, 'msg' => $msg));
    }

    public static function registerAutoload() 
    {
         spl_autoload_register('Utils::objAutoload');
    }

    public static function objAutoload($name)
    {  
        $name = strtolower($name);
        $file = dirname(__FILE__).'/'.$name.'.obj.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }

    function __construct()
    {
        throw new SPXException('Cannot instanciate Utils object');
    }

public static function getVal($ar, $name)
{
    if (isset($ar[$name])) {
        return $ar[$name];
    }
    return;
}

public static function parseFrequency($f)
{
    if ($f >= 2678400) {
        $months = round($f/2678400);

        return $months.'m';
    }
    if ($f >= 604800) {
        $week = round($f/604800);

        return $week.'w';
    }
    if ($f >= 86400) {
        $day = round($f/86400);

        return $day.'d';
    }
    if ($f >= 3600) {
        $hour = round($f/3600);

        return $hour.'h';
    }
    if ($f >= 60) {
        $min = round($f/60);

        return $min.'m';
    }

    return $f.'s';
}

public static function parseVars($c)
{
    $lines = explode(PHP_EOL, $c);
    $rc = array();
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        $v = explode('=', $line, 2);
        if (count($v) == 2) {
            $v[1] = trim(preg_replace('/(^"|"$)/', '', $v[1]));
            $v[0] = trim($v[0]);
            $rc[$v[0]] = $v[1];
        }
    }

    return $rc;
}

public static function parseBool($b)
{
    switch (strtoupper($b)) {
    case "TRUE":
      return 1;
    break;
    case "FALSE":
    default:
      return 0;
    break;
  }
}

}

/* register autoload method */
Utils::registerAutoload();

/* Make sure we have some Mysql definitions */
MySqlCM::getInstance();

/* bootstrap plugin system */
new Plugin();
Plugin::registerPlugins();


