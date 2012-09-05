      <div class="row">
	<h1 class="span12">Server Group <?php echo $obj; ?></h1>
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
          <div class="span4">
           <h3>Actions</h3>
	    <ul class="nav nav-tabs nav-stacked">
	      <li class="dropdown">
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
          <div class="span4">
           <h3>Add Server</h3>
           <div class="input-append">
             <select id="selectServer">
               <option value="-1">Choose a server to add</option>
<?php foreach($a_server as $s) { ?>
               <option value="<?php echo $s->id; ?>"><?php echo $s; ?></option>

<?php } ?>
             </select> <button type="button" class="btn" onClick="addLList('sgroup', <?php echo $obj->id; ?>, 'server', '#selectServer');">Add</button>
           </div>
           <div class="input-append">
             <input id="inputServer" type="text" placeholder="Server hostname Regexp">
             <button type="button" class="btn" onClick="addLListR('sgroup', <?php echo $obj->id; ?>, 'server', '#inputServer');">Add</button>
           </div>
          </div>
          <div class="span8">
           <h3>Free</h3>
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
