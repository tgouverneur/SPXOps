<?php 
  $cols = call_user_func($oc.'::printCols');
  if (!$a_list) $a_list = array();
?>
<?php if (isset($info)) { ?>
	<div class="alert alert-info">
	  <?php echo $info; ?>
	</div>
<?php } ?>
	 <table class="table <?php if (!isset($notStripped)) echo "table-striped"; ?> table-bordered table-hover table-condensed">
	  <thead>
	   <tr>
<?php foreach($cols as $e => $k) { ?>
             <th><?php echo $e; ?></th>
<?php } ?>
<?php if (isset($canMod)) { ?>
             <th></th>
<?php } ?>
<?php if (isset($canDel)) { ?>
             <th></th>
<?php } ?>
<?php if (isset($canView)) { ?>
             <th></th>
<?php } ?>
	   </tr>
	  </thead>
	  <tbody>
<?php foreach($a_list as $e) { $a = $e->toArray(); ?>
           <tr<?php if (isset($a["_color"])) { echo ' class="'.$a['_color'].'" '; } ?>>
   <?php foreach($cols as $v) { 
	   if (preg_match('/^f_/', $v)) {
	     if ($a[$v]) {
	       $fl = '<i class="icon-ok-sign"></i>';
	     } else {
	       $fl = '<i class="icon-remove-sign"></i>';
	     }
    ?>
             <td><?php echo $fl; ?></td>
    <?php
	   } else {
    ?>
             <td><?php echo $a[$v]; ?></td>
    <?php  } ?>
   <?php } ?>
<?php if (isset($canMod)) { ?>
             <td><a href="/edit/w/<?php echo strtolower($oc); ?>/i/<?php echo $e->id; ?>">Edit</a></td>
<?php } ?>
<?php if (isset($canDel)) { ?>
             <td><a href="/del/w/<?php echo strtolower($oc); ?>/i/<?php echo $e->id; ?>">Del</a></td>
<?php } ?>
<?php if (isset($canView)) { ?>
             <td><a href="/view/w/<?php echo strtolower($oc); ?>/i/<?php echo $e->id; ?>">View</a></td>
<?php } ?>
           </tr>
<?php } ?>
	  </tbody>
	 </table>
