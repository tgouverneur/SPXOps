	<div class="page-header">
          <h1>Report</h1>
        </div>
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
	<form method="POST" action="/report" role="form" class="form-horizontal">
	  <div class="form-group">
	    <label for="inputMessage" class="col-sm-2 col-sm-offset-2 control-label">Message</label>
	    <div class="col-sm-6">
	      <textarea class="form-control" id="inputMessage" name="message" rows="10" cols="50" placeholder="Your report here..."></textarea>
	    </div>
	  </div>
	  <div class="form-group">
            <div class="col-sm-offset-4 col-sm-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary">Send</button>
	    </div>
	  </div>
	</form>
