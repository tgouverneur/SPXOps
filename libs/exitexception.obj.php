<?php

/* Types */
define('EXIT_HTTP', 1);
define('EXIT_JSON', 2);
define('EXIT_REDIR', 3);
define('EXIT_DOWN', 4);
define('EXIT_LDOWN', 5);
define('EXIT_LOGIN', 6);

class ExitException extends Exception
{
    public $type = 1; // 1== HTTP, 2==JSON, 3==redirect, 4==download, 5==download large file, 6==login needed
    public $dest = '';
    public $options = array();
    public $fp = null;

    function __construct($msg, $type = 1, $dest = '', $opt = array(), $fp = null) {
        $this->message = $msg;
        $this->type = $type;
        $this->fp = $fp;
        $this->dest = $dest;
        $this->options = $opt;
    }
}

?>
