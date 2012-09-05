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
	<h2><?php echo $action; ?> a Cluster</h2>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/cluster<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
          <div class="control-group">
            <label class="control-label" for="inputName">Name</label>
            <div class="controls">
              <input type="text" name="name" value="<?php echo $obj->name; ?>" id="inputName" placeholder="Cluster Name">
            </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputDescription">Description</label>
            <div class="controls">
              <input type="text" name="description" value="<?php echo $obj->description; ?>" id="inputDescription" placeholder="Cluster Description">
            </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputOptions">Options</label>
            <div class="controls">
              <label class="checkbox">
                <input name="f_upd" type="checkbox" <?php if ($obj->f_upd) { echo "checked"; } ?>>
		<a href="#" rel="tooltip" title="Enable automatic updates of this Cluster">Update</a>
              </label>
            </div>
          </div>
	  <div class="control-group">
            <label class="control-label" for="inputOS">Operating System</label>
            <div class="controls">
              <select name="fk_os" id="selectOS">
	        <option value="-1">Select OS</option>
<?php foreach($oses as $os) { ?>
	        <option <?php if ($obj->fk_os == $os->id) echo "selected"; ?> value="<?php echo $os->id; ?>"><?php echo $os; ?></option>
<?php } ?>
	      </select>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="inputNodes">Nodes</label>
            <div class="controls">
              <select multiple name="a_server[]" id="selectNodes">
<?php foreach($a_server as $s) { ?>
		<option <?php if (isset($obj->a_server[$s->id])) echo "selected"; ?> value="<?php echo $s->id; ?>"><?php echo $s; ?></option>
<?php } ?>
              </select>
            </div>
          </div>
	  <div class="control-group">
	    <div class="controls">
	      <button type="submit" name="submit" value="1" class="btn"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
        </div>
      </div>
