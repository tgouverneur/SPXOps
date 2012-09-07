<?php
if (!isset($obj) || !$obj) { $obj = new RJob(); }
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
if (!isset($edit)) $edit = false;
?>
      <div class="row">
        <div class="span8 offset2">
<?php if (isset($error)) { 
        if (!is_array($error)) {
          $error = array($error);
        }
        foreach($error as $e) {
?>
	<div class="alert alert-error">
	  <button type="button" class="close" data-dismiss="alert">×</button>
	  <strong>Error!</strong> <?php echo $e; ?>
	</div>
<?php   }
      }
?>
	<h2><?php echo $action; ?> a Recurrent Job</h2>
       </div>
      </div>
      <div class="row">
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/rjob<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
        <div class="span5">
          <div class="control-group">
            <label class="control-label" for="inputClass">Class</label>
            <div class="controls">
              <input type="text" name="class" value="<?php echo $obj->class; ?>" id="inputClass" placeholder="Class Name">
            </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputFunction">Function</label>
            <div class="controls">
              <input type="text" name="fct" value="<?php echo $obj->fct; ?>" id="inputFunction" placeholder="Function">
            </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputArgument">Argument</label>
            <div class="controls">
              <input type="text" name="arg" value="<?php echo $obj->arg; ?>" id="inputArgument" placeholder="Argument of the function">
            </div>
          </div>
	  <div class="control-group">
	    <label class="control-label" for="selectFrequency">Frequency</label>
	    <div class="controls">
	      <select name="frequency" id="selectFrequency">
		<option value="-1">Upon request</option>
<?php $f = array(3600, 7200, 14400, 21600, 28800, 43200, 57600, 86400, 172800, 604800, 2678400);
      foreach($f as $freq) { ?>
                <option value="<?php echo $freq; ?>" <?php if ($freq == $obj->frequency) echo "selected"; ?>><?php echo parseFrequency($freq); ?></option>
<?php } ?>
	      </select>
	    </div>
	  </div>
	  <div class="control-group">
	    <div class="controls">
	      <button type="submit" name="submit" value="1" class="btn"><?php echo $action; ?></button>
	    </div>
	  </div>
        </div>
       </form>
      </div>
