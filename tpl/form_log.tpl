<?php
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
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
	<div class="page-header"><h2><?php echo $page['title'].': '.$obj; ?></h2></div>
    <?php if ($edit) { ?>
        <form method="POST" action="/edit/w/log/i/<?php echo $obj->id; ?>" class="form-horizontal">
    <?php } else { ?>
        <form method="POST" action="/log/w/<?php echo get_class($obj); ?>/i/<?php echo $obj->id; ?>" class="form-horizontal">
    <?php } ?>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputName">Msg</label>
            <div class="col-sm-3">
              <textarea class="form-control" name="msg" value="" id="inputMsg" placeholder="Log Message"><?php if ($edit) { echo $obj->msg; } ?></textarea>
            </div>
          </div>
	  <div class="form-group">
	    <div class="col-sm-3 col-sm-offset-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
