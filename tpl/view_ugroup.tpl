<?php 
 if (!isset($a_login)) $a_login = array();
?>
      <div class="row">
	<h1 class="span12">User Group <?php echo $obj; ?></h1>
        <div class="row">
	 <div class="span12">
	  <div class="alert alert-block alert-success fade in" id="success-box" style="display:none;">
	    <button type="button" class="close">×</button>
	    <h4>Success!</h4>
	    <p id="success-msg"></p>
	  </div>
          <div class="alert alert-block fade in" id="warning-box" style="display:none;">
            <button type="button" class="close">×</button>
            <h4>Warning!</h4>
            <p id="warning-msg"></p>
          </div>
          <div class="alert alert-block alert-error fade in" id="error-box" style="display:none;">
            <button type="button" class="close">×</button>
            <h4>Error!</h4>
            <p id="error-msg"></p>
          </div>
	 </div>
	</div>
        <div class="row">
          <div class="span4">
           <h3>Basic Information</h3>
	   <table class="table table-condensed">
	     <tbody>
<?php foreach($obj->htmlDump() as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php } ?>
	     </tbody>
	   </table>
	  </div>
          <div class="span4">
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
          <div class="span4">
           <h3>Actions</h3>
	    <ul class="nav nav-tabs nav-stacked">
	      <li class="dropdown">
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
          <div class="span4">
           <h3>Add Login</h3>
 	   <div class="input-append">
	     <select id="selectLogin">
	       <option value="-1">Choose a login to add</option>
<?php foreach($a_login as $l) { ?>
	       <option value="<?php echo $l->id; ?>"><?php echo $l; ?></option>

<?php } ?>
	     </select> <button type="button" class="btn" onClick="addLList('ugroup', <?php echo $obj->id; ?>, 'login', '#selectLogin');">Add</button>
           </div>
	   <div class="input-append">
             <input id="inputLogin" type="text" placeholder="Login Regexp">
             <button type="button" class="btn" onClick="addLListR('ugroup', <?php echo $obj->id; ?>, 'login', '#inputLogin');">Add</button>
           </div>
          </div>
          <div class="span8">
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
<?php $obj->fetchJT('a_right'); 
      $lm = loginCM::getInstance();
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
		<td><?php if ($lm->o_login->f_admin) { ?><button class="btn btn-primary btn-mini" onClick="saveRight(<?php echo $obj->id; ?>, <?php echo $right->id; ?>);">Save</button><?php } ?></td>
	     </tr>
<?php } ?>
	    </tbody>
	   </table>
          </div>
       </div>
      </div>
      <!-- Logs Modal -->
      <div class="modal large hide fade in" id="logsModal" tabindex="-1" role="dialog" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="logsModalLabel">Disks list</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
