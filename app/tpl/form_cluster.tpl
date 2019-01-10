<?php
if (!isset($obj) || !$obj) { $obj = new Cluster(); }
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
if (!isset($edit)) $edit = false;
if (!isset($oses)) $oses = array();
if (!isset($a_server)) $a_server = array();
if (!isset($obj->fk_os)) $obj->fk_os = -1;
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
	<div class="page-header">
	  <h1><?php echo $action; ?> a Cluster</h1>
	</div>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/cluster<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputName">Name</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="name" value="<?php echo $obj->name; ?>" id="inputName" placeholder="Cluster Name">
            </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputDescription">Description</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="description" value="<?php echo $obj->description; ?>" id="inputDescription" placeholder="Cluster Description">
            </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputOptions">Options</label>
            <div class="col-sm-3">
              <div class="checkbox">
		<label>
                  <input name="f_upd" type="checkbox" <?php if ($obj->f_upd) { echo "checked"; } ?>>
		  <a href="#" rel="tooltip" title="Enable automatic updates of this Cluster">Update</a>
                </label>
	      </div>
            </div>
          </div>
	  <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputOS">Operating System</label>
            <div class="col-sm-3">
              <select class="form-control" name="fk_os" id="selectOS">
	        <option value="-1">Select OS</option>
<?php foreach($oses as $os) { ?>
	        <option <?php if ($obj->fk_os == $os->id) echo "selected"; ?> value="<?php echo $os->id; ?>"><?php echo $os; ?></option>
<?php } ?>
	      </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputNodes">Nodes</label>
            <div class="col-sm-3">
              <select class="form-control" multiple name="a_server[]" id="selectNodes">
<?php foreach($a_server as $s) { ?>
		<option <?php if (isset($obj->a_server[$s->id])) echo "selected"; ?> value="<?php echo $s->id; ?>"><?php echo $s; ?></option>
<?php } ?>
              </select>
            </div>
          </div>
	  <div class="form-group">
	    <div class="col-sm-3 col-sm-offset-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
