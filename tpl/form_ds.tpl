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
      $cols = $obj->printCols('all');
?>
	<div class="page-header">
	  <h1>Display Settings for <?php echo $what; ?></h1>
	</div>
        <form method="POST" role="form" action="/ds/w/server" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-4 col-sm-offset-2 control-label" for="inputDisplaySettings">Tick box for column to be shown</label>
            <div class="col-sm-3">
<?php foreach($cols as $desc => $name) { ?>
              <div class="checkbox">
		<label>
                  <input name="v[<?php echo $name; ?>]" type="checkbox" <?php if (in_array($name, $cfs)) { echo "checked"; } ?>>
		  <a href="#" rel="tooltip" title="<?php echo $desc; ?>"><?php echo $desc; ?></a>
                </label>
	      </div>
<?php } ?>
            </div>
          </div>
	  <div class="form-group">
	    <div class="col-sm-offset-6 col-sm-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $page['action']; ?></button>
	    </div>
	  </div>
	</form>
