<?php

class ExitException extends Exception
{
    public $type = 1; // 1== HTTP, 2==JSON, 3==redirect
    public $dest = '';

    function __construct($msg, $type = 1, $dest = '') {
        $this->message = $msg;
        $this->type = $type;
        $this->dest = $dest;
    }
}
