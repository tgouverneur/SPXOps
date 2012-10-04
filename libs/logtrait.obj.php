<?php

trait logTrait {
  
  public function fetchLogs() {
    $this->a_log = Log::getAll(true, array('o_class' => 'CST:'.get_class(), 'fk_what' => 'CST:'.$this->id), array('DESC:t_add'));
  }

  public function addLog($msg) {
    $lm = loginCM::getInstance();

    $lo = new Log();
    if ($lm->o_login) $lo->fk_login = $lm->o_login->id;

    $lo->msg = $msg;
    $lo->o_class = get_class($this);
    $lo->fk_what = $this->id;
    $lo->insert();
  }
}

?>
