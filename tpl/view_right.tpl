	<div class="page-header"><h1 class="col-md-12">My User rights</h1></div>
        <div class="row">
          <div class="col-md-8">
           <h3>Rights list</h3>
	   <table class="table table-condensed">
	     <thead>
	      <tr><th>Short Name</th><th>Value</th></tr>
 	     </thead>
	     <tbody>
<?php foreach($a_right as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php } ?>
	     </tbody>
	   </table>
	  </div>
	</div>
