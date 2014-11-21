      <div class="row">
	<h1 class="span12">Recurrent Job</h1>
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
          <div class="span8">
           <h3>Recurrent Job Information</h3>
	   <table class="table table-condensed">
	     <tbody>
<?php foreach($obj->htmlDump() as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
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
                  <li><a href="/del/w/rjob/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/edit/w/rjob/i/<?php echo $obj->id; ?>">Edit</a></li>
                </ul>
              </li>
            </ul>
          </div>
       </div>
      </div>
