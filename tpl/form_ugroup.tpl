<?php
if (!isset($obj) || !$obj) { $obj = new UGroup(); }
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
	<h2><?php echo $action; ?> a User Group</h2>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/ugroup<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
          <div class="control-group">
            <label class="control-label" for="inputName">Name</label>
            <div class="controls">
              <input type="text" <?php if ($edit) echo "disabled"; ?> name="name" value="<?php echo $obj->name; ?>" id="inputName" placeholder="Name">
            </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="inputDescription">Description</label>
	    <div class="controls">
	      <input type="text" name="description" value="<?php echo $obj->description; ?>" id="inputDescription" placeholder="Description">
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
