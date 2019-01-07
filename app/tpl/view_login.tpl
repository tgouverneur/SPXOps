<?php 
 if (!isset($a_ugroup)) $a_ugroup = array();
?>
	<div class="page-header"><h1>User <?php echo $obj; ?></h1></div>
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
       <div class="col-md-8">
        <div class="row">
          <div class="col-md-6">
          <h3>User Token</h3>
          <?php if ($obj->o_utoken) { ?>
            <table class="table table-condensed">
             <tbody>
    <?php foreach($obj->o_utoken->htmlDump() as $k => $v) { ?>
              <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
    <?php } ?>
             </tbody>
            </table>
          <?php } else { ?>
          <p>There is no token associated, you may add one using the <b>Token > Add</b> menu.</p>
          <?php } ?>
         </div>
          <div class="col-md-4">
           <h3>Actions</h3>
            <ul class="nav nav-pills nav-stacked">
              <li class="dropdown active">
                  <a href="/del/w/login/i/<?php echo $obj->id; ?>">Delete</a>
              </li>
              <li class="dropdown active">
                  <a href="/edit/w/login/i/<?php echo $obj->id; ?>">Edit</a>
              </li>
              <?php if (LoginCM::getInstance()->o_login->id == $obj->id || LoginCM::getInstance()->o_login->f_admin) { /* Only show token management to self and admin */ ?>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Token  <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="/add/w/utoken">Add</a></li>
                    <li><a href="/token/w/init">Init</a></li>
                    <li><a href="/token/w/check">Check</a></li>
                    <li><a href="/token/w/remove">Remove</a></li>
                </ul>
              </li>
              <?php } ?>
            </ul>
          </div>
        </div>
        <div class="row">
        <?php if ($obj->f_api && (LoginCM::getInstance()->o_login->f_admin || LoginCM::getInstance()->o_login->id == $obj->id)) { ?>
            <div class="col-md-6">
                 <h3>API Key</h3>
                    <table class="table table-condensed">
                      <tbody>
                        <tr>
                         <td><?php echo $obj->getAPIKey(); ?></td>
                       </tr>
                      </tbody>
                  </table>
            </div>
            <?php } ?>
        </div>
        </div>
    </div>
        <div class="row">
          <div class="col-md-4">
           <h3>Groups</h3>
           <table id="LListugroupTable" class="table table-condensed">
             <tbody>
<?php foreach($obj->a_ugroup as $grp) { ?>
	    <tr id="LListugroup<?php echo $grp->id; ?>">
		<td><?php echo $grp->link(); ?></td>
		<td><a href="#" onClick="delLList('login', <?php echo $obj->id; ?>, 'ugroup', <?php echo $grp->id; ?>);">Remove</a></td>
	    </tr>
<?php } ?>
             </tbody>
           </table>
 
           <h3>Add Group</h3>
           <form class="form-inline">
 	   <div class="form-group">
	     <select class="form-control" id="selectGroup">
	       <option value="-1">Choose a group to add</option>
<?php foreach($a_ugroup as $l) { ?>
	       <option value="<?php echo $l->id; ?>"><?php echo $l; ?></option>

<?php } ?>
	     </select> <button type="button" class="btn btn-primary" onClick="addLList('login', <?php echo $obj->id; ?>, 'ugroup', '#selectGroup');">Add</button>
           </div>
	   </form>
           <form class="form-inline">
	   <div class="form-group">
             <input class="form-control" id="inputGroup" type="text" placeholder="Group Regexp">
             <button type="button" class="btn btn-primary" onClick="addLListR('login', <?php echo $obj->id; ?>, 'ugroup', '#inputGroup');">Add</button>
           </div>
 	   </form>
          </div>
          <div class="col-md-8">
           <h3>Last Activities</h3>
	     <table class="table table-condensed">
	      <tbody>
<?php foreach($a_act as $act) { ?>
		<tr><td><?php echo date('d-m-Y H:m:s', $act->t_add); ?></td><td><?php echo $act; ?></td></tr>
<?php } ?>
	      </tbody>
	     </table>
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
      <script class="code" type="text/javascript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
