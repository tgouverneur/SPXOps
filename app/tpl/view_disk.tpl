	<div class="page-header"><h1>Disk</h1></div>
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
          <div class="col-md-8">
           <h3>Disk Information</h3>
	   <table class="table table-condensed">
	     <tbody>
<?php foreach($obj->htmlDump() as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php } ?>
	     </tbody>
	   </table>
	  </div>
          <div class="col-md-4">
           <h3>Actions</h3>
            <ul class="nav nav-pills nav-stacked">
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Placeholder <b class="caret"></b></a>
                <ul class="dropdown-menu"><!--
                  <li><a href="/del/w/rjob/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/edit/w/rjob/i/<?php echo $obj->id; ?>">Edit</a></li>-->
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
