<?php 
 if (!isset($a_login)) $a_login = array();
?>
	<div class="page-header"><h1>User Group <?php echo $obj; ?></h1></div>
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
           <h3>Members</h3>
           <table id="LListloginTable" class="table table-condensed">
             <tbody>
<?php foreach($obj->a_login as $login) { ?>
	    <tr id="LListlogin<?php echo $login->id; ?>">
		<td><?php echo $login->link(); ?></td>
		<td><a href="#" onClick="delLList('ugroup', <?php echo $obj->id; ?>, 'login', <?php echo $login->id; ?>);">Remove</a></td>
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
                  <li><a href="/del/w/ugroup/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/edit/w/ugroup/i/<?php echo $obj->id; ?>">Edit</a></li>
	        </ul>
	      </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="col-md-4">
	   <h3>Alerts</h3>
	   <table class="table table-condensed">
            <thead>
             <tr>
                <th>Alert Type</th>
                <th>Enabled</th>
                <th></th>
             </tr>
            </thead>
            <tbody>
<?php 
  foreach($a_alerttype as $at) {
?>
             <tr>
                <td><?php echo $at->name; ?></td>
                <td><input type="checkbox" id="at_<?php echo $obj->id.'_'.$at->id; ?>" <?php if ($obj->isAlertType($at)) echo 'checked'; ?>/></td>
                <td><button class="btn btn-primary btn-xs" onClick="saveATS(<?php echo $obj->id; ?>, <?php echo $at->id; ?>);">Save</button></td>
             </tr>
<?php } ?>
	    </tbody>
            <thead>
             <tr>
                <th>Server Grp</th>
                <th>Enabled</th>
                <th></th>
             </tr>
            </thead>
	    <tbody>
<?php foreach($a_sgroup as $sg) { ?>
             <tr>
                <td><?php echo $sg->name; ?></td>
                <td><input type="checkbox" id="sg_<?php echo $obj->id.'_'.$sg->id; ?>" <?php if ($obj->isSGroup($sg)) echo 'checked'; ?>/></td>
                <td><button class="btn btn-primary btn-xs" onClick="saveATG(<?php echo $obj->id; ?>, <?php echo $sg->id; ?>);">Save</button></td>
             </tr>
<?php } ?>
	    </tbody>
	   </table>
           <h3>Add Login</h3>
           <form class="form-inline">
 	   <div class="form-group">
	     <select id="selectLogin" class="form-control">
	       <option value="-1">Choose a login to add</option>
<?php foreach($a_login as $l) { ?>
	       <option value="<?php echo $l->id; ?>"><?php echo $l; ?></option>

<?php } ?>
	     </select> <button type="button" class="btn btn-primary" onClick="addLList('ugroup', <?php echo $obj->id; ?>, 'login', '#selectLogin');">Add</button>
           </div>
	   </form>
           <form class="form-inline">
	   <div class="form-group">
             <input class="form-control" id="inputLogin" type="text" placeholder="Login Regexp">
             <button type="button" class="btn btn-primary" onClick="addLListR('ugroup', <?php echo $obj->id; ?>, 'login', '#inputLogin');">Add</button>
           </div>
	   </form>
          </div>
          <div class="col-md-8">
           <h3>Rights</h3>
           <table class="table table-condensed">
	    <thead>
	     <tr>
		<th>Label</th>
		<th>View</th>
		<th>Add</th>
		<th>Edit</th>
		<th>Del</th>
		<th></th>
	     </tr>
	    </thead>
	    <tbody>
<?php 
      $lm = LoginCM::getInstance();
      foreach($a_right as $right) { 
	$l = $obj->getRight($right);
        $view_r = $l & R_VIEW;
        $add_r = $l & R_ADD;
        $edit_r = $l & R_EDIT;
        $del_r = $l & R_DEL;
?>
	     <tr>
		<td><?php echo $right->name; ?></td>
		<td><input <?php if (!$lm->o_login->f_admin) echo "disabled"; ?> type="checkbox" id="view_<?php echo $obj->id.'_'.$right->id; ?>" <?php if ($view_r) echo 'checked'; ?>/></td>
		<td><input <?php if (!$lm->o_login->f_admin) echo "disabled"; ?> type="checkbox" id="add_<?php echo $obj->id.'_'.$right->id; ?>" <?php if ($add_r) echo 'checked'; ?>/></td>
		<td><input <?php if (!$lm->o_login->f_admin) echo "disabled"; ?> type="checkbox" id="edit_<?php echo $obj->id.'_'.$right->id; ?>" <?php if ($edit_r) echo 'checked'; ?>/></td>
		<td><input <?php if (!$lm->o_login->f_admin) echo "disabled"; ?> type="checkbox" id="del_<?php echo $obj->id.'_'.$right->id; ?>" <?php if ($del_r) echo 'checked'; ?>/></td>
		<td><?php if ($lm->o_login->f_admin) { ?><button class="btn btn-primary btn-xs" onClick="saveRight(<?php echo $obj->id; ?>, <?php echo $right->id; ?>);">Save</button><?php } ?></td>
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
             <button type="button" class="close" data-dismiss="modal"><col-md- aria-hidden="true">&times;</col-md-><col-md- class="sr-only">Close</col-md-></button>
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
      </script>
      <script class="code" type="text/javascript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
