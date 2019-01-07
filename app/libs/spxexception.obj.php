<?php

class SPXException extends Exception
{
    protected $logMe = true;
    protected static $_logfd = 0;

    public static function openLog() {
        if (!(SPXException::$_logfd = fopen(Config::$exception_log, 'a+'))) {
            return;
        }
    }

    public static function closeLog() {
        if (SPXException::$_logfd) {
            fclose(SPXException::$_logfd);
            SPXException::$_logfd = 0;
        }
        return;
    }

    public function log() {
        if (!SPXException::$_logfd) {
            SPXException::openLog();
        }
        $pid = getmypid();
        $uid = posix_getuid();
        $uname = posix_getlogin();
        fprintf(SPXException::$_logfd, "[%s] Exception received. PID=%d UID=%d USER=%s\n\t* Message: %s\n\t* Stack trace:\n%s\n", 
            date("Y-m-d H:i:s"), $pid, $uid, $uname, $this->getMessage(), preg_replace('/^/m', "\t\t", $this->getTraceAsString()));
        if (SPXException::$_logfd) {
            SPXException::closeLog();
        }
    }

    public function __construct($message = null, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->logMe = true;
        if ($this->logMe) {
            $this->log();
        }
    }
}
