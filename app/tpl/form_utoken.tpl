<?php
if (!isset($obj) || !$obj) { $obj = new Login(); }
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
if (!isset($edit)) $edit = false;
?>
<?php if (isset($error)) { 
        if (!is_array($error)) {
          $error = array($error);
        }
        foreach($error as $e) {
?>
        <div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <strong>Error!</strong> <?php echo $e; ?>
        </div>
<?php   }
      }
?>
	<div class="page-header"><h1><?php echo $action; ?> a User Token</h1></div>
        <div class="row"><div class="col-md-4 col-md-offset-4">
        <p><b>NOTE:</b> leave secret blank to have a random secret generated.</p>
        </div>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/utoken<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
	   <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputOTPType">TOTP/HOTP</label>
	    <div class="col-sm-3">
	      <select class="form-control" name="type" id="inputOTPType">
            <option value="none" selected>none</option>
            <option value="1" <?php if (isset($obj) && $obj->type == 1) { echo 'selected'; } ?>>HOTP</option>
            <option value="2" <?php if (isset($obj) && $obj->type == 2) { echo 'selected'; } ?>>TOTP</option>
          </select>
	    </div>
	   </div>
	   <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputOtpSecret">Secret</label>
	    <div class="col-sm-3">
	      <input class="form-control" type="text" name="secret" id="inputOtpSecret" value="<?php if (isset($obj)) { echo $obj->secret; } ?>" placeholder="OTP Secret">
	    </div>
	   </div>
	   <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputOtpCounter">HOTP Counter</label>
	    <div class="col-sm-3">
	      <input class="form-control" type="text" name="counter" id="inputCounter" value="<?php if (isset($obj)) { echo $obj->counter; } ?>" placeholder="OTP Counter">
	    </div>
	   </div>
	   <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputNDigit"># of Digit</label>
	    <div class="col-sm-3">
	      <select class="form-control" name="digit" id="inputNDigit">
            <option value="6" <?php if (isset($obj) && $obj->digit == 6) { echo 'selected'; } ?>>6</option>
            <option value="8" <?php if (isset($obj) && $obj->digit == 8) { echo 'selected'; } ?>>8</option>
          </select>
	    </div>
	   </div>
	  <div class="form-group">
	    <div class="col-sm-offset-5 col-sm-3">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
