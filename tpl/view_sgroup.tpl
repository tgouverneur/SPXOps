	<div class="page-header"><h1>Server Group <?php echo $obj; ?></h1></div>
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
          <div class="col-md-4">
           <h3>Basic Information</h3>
	   <table class="table table-condensed">
	     <tbody>
<?php foreach($obj->htmlDump() as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php } ?>
	     </tbody>
	   </table>
	  </div>
          <div class="col-md-4">
           <h3>Server Members</h3>
           <table id="LListserverTable" class="table table-condensed">
             <tbody>
<?php foreach($obj->a_server as $server) { ?>
            <tr id="LListserver<?php echo $server->id; ?>">
                <td><?php echo $server->link(); ?></td>
                <td><a href="#" onClick="delLList('sgroup', <?php echo $obj->id; ?>, 'server', <?php echo $server->id; ?>);">Remove</a></td>
            </tr>
<?php } ?>
             </tbody>
           </table>
          </div>
          <div class="col-md-4">
           <h3>Actions</h3>
	    <ul class="nav nav-pills nav-stacked">
	      <li class="dropdown active">
		<a class="dropdown-toggle" data-toggle="dropdown" href="#">Database <b class="caret"></b></a>
	        <ul class="dropdown-menu">
                  <li><a href="/del/w/sgroup/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/edit/w/sgroup/i/<?php echo $obj->id; ?>">Edit</a></li>
	        </ul>
	      </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="col-md-4">
           <h3>Add Server</h3>
           <form class="form-inline">
           <div class="form-group">
             <select class="form-control" id="selectServer">
               <option value="-1">Choose a server to add</option>
<?php foreach($a_server as $s) { ?>
               <option value="<?php echo $s->id; ?>"><?php echo $s; ?></option>

<?php } ?>
             </select> <button type="button" class="btn btn-primary" onClick="addLList('sgroup', <?php echo $obj->id; ?>, 'server', '#selectServer');">Add</button>
           </div>
	   </form>
           <form class="form-inline">
           <div class="form-group">
             <input class="form-control" id="inputServer" type="text" placeholder="Server hostname Regexp">
             <button type="button" class="btn btn-primary" onClick="addLListR('sgroup', <?php echo $obj->id; ?>, 'server', '#inputServer');">Add</button>
           </div>
          </div>
          </form>
          <div class="col-md-8">
           <h3>VM Members</h3>
           <table id="LListserverTable" class="table table-condensed">
             <tbody>
<?php foreach($obj->a_vm as $server) { ?>
            <tr id="LListvm<?php echo $server->id; ?>">
                <td><?php echo $server->link(); ?></td>
                <td><a href="#" onClick="delLList('sgroup', <?php echo $obj->id; ?>, 'vm', <?php echo $server->id; ?>);">Remove</a></td>
            </tr>
<?php } ?>
             </tbody>
           </table>
 
          </div>
       </div>
      </div>
      <!-- Logs Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="logsModal" aria-labelledby="logsModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="logsModalLabel">Logs entries:</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <script class="code" type="text/javascript">
        $('.logsModalLink').click(function(e) {
          var modal = $('#logsModal'), modalBody = $('#logsModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
