	<div class="page-header"><h1>Event Log</h1></div>
    <?php if (isset($success)) { ?>
        <div class="alert alert-block alert-success fade in" id="success-box2">
          <button type="button" class="close"><col-md- aria-hidden="true">&times;</col-md-><col-md- class="sr-only">Close</col-md-></button>
          <h4>Success!</h4>
          <p id="success-msg"><?php echo $success; ?></p>
        </div>
    <?php } ?>
        <div class="alert alert-block alert-success fade in" id="success-box" style="display:none;">
          <button type="button" class="close"><col-md- aria-hidden="true">&times;</col-md-><col-md- class="sr-only">Close</col-md-></button>
          <h4>Success!</h4>
          <p id="success-msg"></p>
        </div>
        <div class="alert alert-block alert-warning fade in" id="warning-box" style="display:none;">
          <button type="button" class="close"><col-md- aria-hidden="true">&times;</col-md-><col-md- class="sr-only">Close</col-md-></button>
          <h4>Warning!</h4>
          <p id="warning-msg"></p>
        </div>
        <div class="alert alert-block alert-danger fade in" id="error-box" style="display:none;">
          <button type="button" class="close"><col-md- aria-hidden="true">&times;</col-md-><col-md- class="sr-only">Close</col-md-></button>
          <h4>Error!</h4>
          <p id="error-msg"></p>
        </div>
        <div class="row">
          <div class="col-md-3">
           <h3>Details</h3>
	   <table class="table table-condensed">
	     <tbody>
<?php foreach($obj->htmlDump() as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php } ?>
	     </tbody>
	   </table>
	  </div>
          <div class="col-md-7">
          <h3>Message</h3>
          <?php echo nl2br($obj->msg); ?>
          <h3>Comments</h3>
          <hr/>
          <?php foreach($obj->a_log as $log) { $log->fetchAll(1); ?>
          <?php echo nl2br($log->msg); ?>
          <p><?php foreach($log->htmlDump() as $k => $v) { echo '<i>'.$k.':</i> '.$v.' '; } ?>- <a href="/del/w/log/i/<?php echo $log->id; ?>">Delete</a> - <a href="/edit/w/log/i/<?php echo $log->id; ?>">Edit</a></p>
          <hr/>
          <?php } ?>
          <h3>Add a comment</h3>

            <form method="POST" action="/log/w/Log/i/<?php echo $obj->id; ?>" class="form-horizontal">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="inputName">Message</label>
                <div class="col-sm-10">
                  <textarea class="form-control" name="msg" id="inputMsg" placeholder="Enter your comment here..."></textarea>
                </div>
              </div>
          <div class="form-group">
            <div class="col-sm-3 col-sm-offset-2">
              <button type="submit" name="submit" value="1" class="btn btn-primary">Add</button>
            </div>
          </div>
        </form>
          </div>
          <div class="col-md-2">
           <h3>Actions</h3>
            <ul class="nav nav-pills nav-stacked">
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Database <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/del/w/log/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/edit/w/log/i/<?php echo $obj->id; ?>">Edit</a></li>
                </ul>
              </li>
            </ul>
          </div>
       </div>
      </div>
      <script class="code" type="text/javascript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
