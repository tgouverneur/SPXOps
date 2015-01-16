        <div class="page-header"><h1><?php echo $obj; ?> Check Dashboard</h1></div>
        <div class="alert alert-block alert-success fade in" id="success-box" style="display:none;">
          <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Success!</h4>
          <p id="success-msg"></p>
        </div>
        <div class="alert alert-block alert-warning fade in" id="warning-box" style="display:none;">
          <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Warning!</h4>
          <p id="warning-msg"></p>
        </div>
        <div class="alert alert-block alert-danger fade in" id="error-box" style="display:none;">
          <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Error!</h4>
          <p id="error-msg"></p>
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
	  <td><?php if (!empty($r->details)) { ?><a data-toggle="modal" data-target="#crDetailsModal" data-id="<?php echo $r->id; ?>" class="btn-xs btn btn-primary btn-mini"href="#">Details</a><?php } ?></td>
	 </tr>
<?php }	?>
	</tbody>
	</table>
        <div class="modal fade" id="crDetailsModal" tabindex="-1" role="dialog" aria-labelledby="crDetailsLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="crDetailsLabel">Result details</h4>
              </div>
              <div class="modal-body">
  		<p></p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
      <script class="code" type="text/javascript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
	$('#crDetailsModal').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var recipient = button.data('id')
          $.getJSON("/rpc",{w: "cr", i: recipient}, function(d){
	    var modal = $('#crDetailsModal')
 	    modal.find('.modal-body p').html(d['details'])
	  })
	})
      </script>

