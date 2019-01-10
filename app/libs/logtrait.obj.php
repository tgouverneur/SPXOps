<?php

trait logTrait
{

  public function fetchLogs()
  {
      $this->a_log = Log::getAll(true, array('o_class' => 'CST:'.get_class(), 'fk_what' => 'CST:'.$this->id), array('DESC:t_add'));
  }

  public function addLog($msg, $when = -1)
  {
      $lm = LoginCM::getInstance();
      $lo = new Log();
      if ($lm->o_login) {
          $lo->fk_login = $lm->o_login->id;
      } else {
          $lo->fk_login = -1; /* system log */
      }
      $lo->msg = $msg;
      $lo->o_class = get_class($this);
      $lo->fk_what = $this->id;
      $lo->t_add = $when;
      $lo->insert();
      SlackMSG::sendMessage('('.$lo->o_class.')['.$this.']: '.$msg);
  }
}
