<?php 
  if (!isset($cfs) || !count($cfs)) { /* default fields list */
    $cfs = array();
  }
  $cols = call_user_func($oc.'::printCols', $cfs);
  if (!$a_list) $a_list = array();
?>
        <div class="page-header">
  	  <h1>List of <?php echo $what; ?></h1>
        </div>
        <div class="row">
          <div class="col-md-12">
  	   <table id="datatable" class="table <?php if (!isset($notStripped)) echo "table-striped"; ?> table-bordered table-hover table-condensed">
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
<?php if (isset($canKill)) { ?>
               <th></th>
<?php } ?>
	     </tr>
	    </thead>
	    <tbody>
<?php foreach($a_list as $e) { $a = $e->toArray($cols); ?>
             <tr<?php if (isset($a["_color"])) { echo ' class="'.$a['_color'].'" '; } ?>>
   <?php foreach($cols as $v) { 
	   if (preg_match('/^f_/', $v)) {
	     if ($a[$v]) {
	       $fl = '<span class="glyphicon glyphicon-ok-sign"></span>';
	     } else {
	       $fl = '<span class="glyphicon glyphicon-remove-circle"></span>';
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
<?php if (isset($canKill)) { ?>
               <td><a href="/del/w/<?php echo strtolower($oc); ?>/i/<?php echo $e->id; ?>">Kill</a></td>
<?php } ?>
             </tr>
<?php } ?>
	    </tbody>
	   </table>
         </div>
	</div>
<?php if (isset($actions)) { ?>
 	<div class="page-header">
          <h1>Actions</h1>
    </div>
    <div class="row">
      <div class="col-md-12">
        <ul class="nav nav-pills nav-stacked">
<?php foreach($actions as $name => $link) { ?>
          <li class="col-md-4" role="presentation"><a href="<?php echo $link; ?>"><?php echo $name; ?></a></li>
<?php } ?>
        </ul>
      </div>
    </div>
<?php } ?>
