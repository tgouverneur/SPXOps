<?php

class ExitException extends Exception
{
    public $type = 1; // 1== HTTP, 2==JSON

    function __construct($msg, $type = 1) {
        $this->message = $msg;
        $this->type = $type;
    }
}
