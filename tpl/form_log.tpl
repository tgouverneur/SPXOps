<?php
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
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
	<h2><?php echo $page['title'].': '.$obj; ?></h2>
        <form method="POST" action="/log/w/<?php echo get_class($obj); ?>/i/<?php echo $obj->id; ?>" class="form-horizontal">
          <div class="control-group">
            <label class="control-label" for="inputName">Msg</label>
            <div class="controls">
              <input type="text" name="msg" value="" id="inputMsg" placeholder="Log Message">
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
