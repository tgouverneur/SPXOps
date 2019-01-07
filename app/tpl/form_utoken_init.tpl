<?php 
if (!isset($action)) {
    $action = 'init';
}
      if (isset($error)) { 
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
<?php if (isset($msg)) { ?>
        <div class="alert alert-block alert-success fade in" id="success-box">
	  <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Success!</h4>
          <p id="success-msg"><?php echo $msg; ?></p>
        </div>
<?php } ?>
        <div class="alert alert-block alert-warning fade in" id="warning-box" style="display:none;">
	  <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Warning!</h4>
          <p id="warning-msg"></p>
        </div>
        <div class="page-header"><h1>User Token: Initialization</h1></div>
    <?php if (isset($qrcode)) { ?>
        <div class="row">
         <div class="col-md-4 col-md-offset-4">
             <canvas id="qrcode" width="300" height="300"></canvas>
             <br/>
             <br/>
             <br/>
         </div>
        </div>
    <?php } ?>
        <form method="POST" action="/token/w/<?php echo $action; ?>" class="form-horizontal">
	   <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputOTP">Token Value:</label>
	    <div class="col-sm-3">
	      <input class="form-control" type="text" name="OTPValue" id="inputOtpSecret" placeholder="00000000">
	    </div>
	   </div>
	  <div class="form-group">
	    <div class="col-sm-offset-5 col-sm-3">
	      <button type="submit" name="submit" value="1" class="btn btn-primary">Next</button>
	    </div>
	  </div>
	</form>
    <?php if (isset($qrcode)) { ?>
    <script class="code" type="text/javascript">
    $(document).ready(function(){
        $('#qrcode').qrcode({
           "size": 300,
           "color": "#3a3",
           "text": "<?php echo $qrcode; ?>"
        });
    });
    </script>
    <?php } ?>
