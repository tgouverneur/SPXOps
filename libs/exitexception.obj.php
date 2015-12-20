<?php

class ExitException extends Exception
{
    public $type = 1; // 1== HTTP, 2==JSON, 3==redirect, 4==download, 5==download large file
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
