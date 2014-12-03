	<div class="page-header"><h1>Check <?php echo $obj; ?></h1></div>
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
           <h3>Groups</h3>
           <table id="LListsgroupTable" class="table table-condensed">
	     <caption>Will be done on:</caption>
             <tbody>
<?php foreach($obj->a_sgroup as $grp) { if ($obj->f_except[''.$grp]) continue; ?>
            <tr id="LListsgroup<?php echo $grp->id; ?>">
                <td><?php echo $grp->link(); ?></td>
                <td><a href="#" onClick="delLList('check', <?php echo $obj->id; ?>, 'sgroup', <?php echo $grp->id; ?>);">Remove</a></td>
            </tr>
<?php } ?>  
             </tbody>
           </table>
           <table id="LListesgroupTable" class="table table-condensed">
             <caption>Except for members of:</caption>
             <tbody>
<?php foreach($obj->a_sgroup as $grp) { if (!$obj->f_except[''.$grp]) continue; ?>
            <tr id="LListesgroup<?php echo $grp->id; ?>">
                <td><?php echo $grp->link(); ?></td>
                <td><a href="#" onClick="delLList('check', <?php echo $obj->id; ?>, 'esgroup', <?php echo $grp->id; ?>);">Remove</a></td>
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
                  <li><a href="/del/w/check/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/edit/w/check/i/<?php echo $obj->id; ?>">Edit</a></li>
                  <li><a href="/log/w/check/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
	        </ul>
	      </li>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/modallist/w/logs/o/check/i/<?php echo $obj->id; ?>" class="logsModalLink">View Logs</a></li>
                  <li><a href="/modallist/w/results/i/<?php echo $obj->id; ?>" class="resultsModalLink">View Result</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="col-md-4">
           <h3>Add Groups</h3>
           <form class="form-inline">
           <div class="form-group">
	     <label class="sr-only" for="selectEGroup">Group to Add</label>
             <select class="form-control input-md" id="selectGroup">
               <option value="-1">Choose a group to add</option>
<?php foreach($a_sgroup as $l) { ?>
               <option value="<?php echo $l->id; ?>"><?php echo $l; ?></option>
<?php } ?>  
             </select> <button type="button" class="btn btn-primary" onClick="addLList('check', <?php echo $obj->id; ?>, 'sgroup', '#selectGroup');">Add</button>
           </div>
          </form>
           <form class="form-inline">
           <div class="form-group">
	     <label class="sr-only" for="selectEGroup">Group to Exempt</label>
             <select class="form-control" id="selectEGroup">
               <option value="-1">Choose a group to exempt</option>
<?php foreach($a_sgroup as $l) { ?>
               <option value="<?php echo $l->id; ?>"><?php echo $l; ?></option>

<?php } ?>  
             </select> <button type="button" class="btn btn-primary" onClick="addLList('check', <?php echo $obj->id; ?>, 'esgroup', '#selectEGroup');">Add</button>
          </div>
          </div>
          </form>
          <div class="col-md-8">
           <h3>LUA Code</h3>
	    <pre class="pre-scrollable">
<?php echo $obj->lua; ?>
	    </pre>
          </div>
       </div>
      </div>
      <!-- Result Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="resultsModal" aria-labelledby="resultsModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="resultsModalLabel">Checks Results:</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
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
        $('.resultsModalLink').click(function(e) {
          var modal = $('#resultsModal'), modalBody = $('#resultsModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.logsModalLink').click(function(e) {
          var modal = $('#logsModal'), modalBody = $('#logsModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
      </script>
      <script class="code" type="text/javascript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
