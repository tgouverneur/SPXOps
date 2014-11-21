      <div class="row">
        <div class="span12">
          <h1><?php echo $obj; ?> Check Dashboard</h1>
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
	  <table class="table table-bordered">
	  <thead>
	   <tr>
	    <th>RC</th>
	    <th>Check Name</th>
	    <th>Message</th>
	    <th>ACK</th>
	    <th>ACK By</th>
	    <th>Done on</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
<?php foreach($obj->a_lr as $r) { ?>
	   <tr<?php echo ' class="'.Result::colorRC($r->rc).'" '; ?>>
	    <td><?php echo Result::colorRC($r->rc); ?></td>
	    <td><?php echo $r->o_check->name; ?></td>
	    <td><?php echo $r->message; ?></td>
	    <td><?php echo ($r->f_ack)?'<i class="icon-ok-sign"></i>':'<i class="icon-remove-sign"></i>'; ?></td>
	    <td id="ackBtn<?php echo $r->id; ?>"><?php echo $r->ackBy(); ?></td>
	    <td><?php echo date('d-m-Y H:m:s', $r->t_upd); ?></td>
	    <td><?php if (!empty($r->details)) { ?><a href="#">Details</a><?php } ?></td>
	   </tr>
<?php }	?>
	  </tbody>
	  </table>
        </div>
      </div>
