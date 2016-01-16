    <div class="alert alert-info alert-block">
	  <h4>Message!</h4>
	  <?php echo $msg; ?>
      <?php if (isset($redir) && !empty($redir)) { ?>
          <br/>You are going to be redirected to the requested page, if you are not redirected within 5 seconds, click <a href="<?php echo $redir; ?>">here</a>
          <script type="text/javascript">
              setTimeout(function() { 
                  window.location.href = '<?php echo $redir; ?>';
                   }, 4000);
          </script>
      <?php } ?>
	</div>
