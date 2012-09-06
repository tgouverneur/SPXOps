      <div class="row">
	<h1 class="span12">Check <?php echo $obj; ?></h1>
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
          <div class="span4">
           <h3>Actions</h3>
	    <ul class="nav nav-tabs nav-stacked">
	      <li class="dropdown">
		<a class="dropdown-toggle" data-toggle="dropdown" href="#">Database <b class="caret"></b></a>
	        <ul class="dropdown-menu">
                  <li><a href="/del/w/check/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/edit/w/check/i/<?php echo $obj->id; ?>">Edit</a></li>
                  <li><a href="/add/w/logentry/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
	        </ul>
	      </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a data-toggle="modal" href="/modallist/w/logs/i/<?php echo $obj->id; ?>" data-target="#logsModal">View Logs</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="span4">
           <h3>Add Groups</h3>
           <div class="input-append">
             <select id="selectGroup">
               <option value="-1">Choose a group to add</option>
<?php foreach($a_sgroup as $l) { ?>
               <option value="<?php echo $l->id; ?>"><?php echo $l; ?></option>

<?php } ?>  
             </select> <button type="button" class="btn" onClick="addLList('check', <?php echo $obj->id; ?>, 'sgroup', '#selectGroup');">Add</button>
           </div>
           <div class="input-append">
             <select id="selectEGroup">
               <option value="-1">Choose a group to exempt</option>
<?php foreach($a_sgroup as $l) { ?>
               <option value="<?php echo $l->id; ?>"><?php echo $l; ?></option>

<?php } ?>  
             </select> <button type="button" class="btn" onClick="addLList('check', <?php echo $obj->id; ?>, 'esgroup', '#selectEGroup');">Add</button>
           </div>
          </div>
          <div class="span8">
           <h3>LUA Code</h3>
	    <pre class="pre-scrollable">
<?php echo $obj->lua; ?>
	    </pre>
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
