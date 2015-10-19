<?php

trait checkTrait {

    /* Check system */
    public $a_check = array();
    public $a_lr = array();
    public $rc = 0;
    public $ack = false;

    public function buildCheckList($force = false)
    {
        $checks = Check::getAll(true);
        $now = time();
        $this->a_check = array();

        foreach ($checks as $check) {

          /* Check Groups */
          $check->fetchJT('a_sgroup');
          $f_group = 0;
          $f_egroup = 0;

            foreach ($check->a_sgroup as $grp) {
                if ($this->isInJT('a_sgroup', $grp)) {
                    if ($check->f_except[''.$grp]) {
                        $f_egroup = 1;
                    } else {
                        $f_group = 1;
                    }
                }
            }
            if (!$f_group || $f_egroup) {
                continue;
            }

            $this->a_lr[$check->id] = Result::getLast($check, $this);

            if ($this->a_lr[$check->id] === null) {
                array_push($this->a_check, $check);
                continue;
            }
            $this->a_lr[$check->id]->o_check = $check;

            if ($force) { /* don't take timestamp into account */
                array_push($this->a_check, $check);
                continue;
            }
            if (($now - $this->a_lr[$check->id]->t_upd) >= $check->frequency) {
                array_push($this->a_check, $check);
            }
        }
    }

}
?>
