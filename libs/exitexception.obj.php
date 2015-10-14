<?php

class ExitException extends Exception
{
    public $type = 1; // 1== HTTP, 2==JSON, 3==redirect, 4==download
    public $dest = '';
    public $options = array();

    function __construct($msg, $type = 1, $dest = '', $opt = array()) {
        $this->message = $msg;
        $this->type = $type;
        $this->dest = $dest;
        $this->options = $opt;
    }
}
