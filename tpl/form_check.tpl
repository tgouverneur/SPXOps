<?php
if (!isset($obj) || !$obj) { $obj = new Check(); }
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
if (!isset($edit)) $edit = false;
if (!isset($susers)) $susers = array();
if (!isset($susers)) $edit = false;
if (!isset($pservers)) $pservers = array();
?>
<?php if (isset($error)) { 
        if (!is_array($error)) {
          $error = array($error);
        }
        foreach($error as $e) {
?>
        <div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><col-sm- aria-hidden="true">&times;</col-sm-><col-sm- class="sr-only">Close</col-sm-></button>
          <strong>Error!</strong> <?php echo $e; ?>
        </div>
<?php   }
      }
?>
	<div class="page-header"><h1><?php echo $action; ?> a Check</h1></div>
        <div class="row">
        <form role="form" method="POST" action="/<?php echo strtolower($action); ?>/w/check<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
        <div class="col-sm-6">
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputName">Name</label>
            <div class="col-sm-7">
              <input class="form-control" type="text" <?php if ($edit) echo "disabled"; ?> name="name" value="<?php echo $obj->name; ?>" id="inputName" placeholder="Check Name">
            </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputDescription">Description</label>
            <div class="col-sm-7">
              <input class="form-control" type="text" name="description" value="<?php echo $obj->description; ?>" id="inputDescription" placeholder="Check Description">
            </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputMSGError">Error Message</label>
            <div class="col-sm-7">
              <input class="form-control" type="text" name="m_error" value="<?php echo $obj->m_error; ?>" id="inputMSGError" placeholder="Message in case of error">
            </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputMSGWarn">Warning Message</label>
            <div class="col-sm-7">
              <input class="form-control" type="text" name="m_warn" value="<?php echo $obj->m_warn; ?>" id="inputMSGWarn" placeholder="Message in case of warning">
            </div>
          </div>
	  <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="selectFrequency">Frequency</label>
	    <div class="col-sm-7">
	      <select class="form-control" name="frequency" id="selectFrequency">
		<option value="-1">Upon request</option>
<?php $f = array(3600, 7200, 14400, 21600, 28800, 43200, 57600, 86400, 172800, 604800, 2678400);
      foreach($f as $freq) { ?>
		<option value="<?php echo $freq; ?>" <?php if ($freq == $obj->frequency) echo "selected"; ?>><?php echo Utils::parseFrequency($freq); ?></option>
<?php } ?>
	      </select>
	    </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputOptions">Options</label>
            <div class="col-sm-7">
              <div class="checkbox">
		<label>
                 <input name="f_noalerts" type="checkbox" <?php if ($obj->f_noalerts) { echo "checked"; } ?>>
		 <a href="#" rel="tooltip" title="Disable alerting for this check">Alerts Disabled</a>
                </label>
  	       </div>
              <div class="checkbox">
                <label>
                 <input name="f_root" type="checkbox" <?php if ($obj->f_root) { echo "checked"; } ?>>
                 <a href="#" rel="tooltip" title="This check need root access">Need root</a>
                </label>
               </div>
              <div class="checkbox">
                <label>
                 <input name="f_vm" type="checkbox" <?php if ($obj->f_vm) { echo "checked"; } ?>>
                 <a href="#" rel="tooltip" title="This check supports VMs">VM Support</a>
                </label>
               </div>
            </div>
          </div>
	  <div class="form-group">
	    <div class="col-sm-7 col-sm-offset-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $action; ?></button>
	    </div>
	  </div>
        </div>
        <div class="col-sm-6">
         <textarea name="lua" rows="20" class="form-control input-xxlarge"><?php if (!empty($obj->lua)) echo $obj->lua; ?></textarea>
        </div>
       </form>
       </div>
