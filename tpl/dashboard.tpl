<?php
 if (!isset($columns)) $columns = 8;
 $total = count($a_list);
 $lines = $total / $columns;
 $keys = array_keys($a_list);
 $k = 0;
?>
      <div class="row">
        <div class="span12">
          <h1>Dashboard</h1>
	  <table class="table table-bordered">
	  <tbody>
  <?php for ($i=0; $i<$lines; $i++) { ?>
   <tr>
     <?php for ($j=0; $j<$columns && $k < $total; $j++) { ?>
     <td class="td<?php echo Result::colorRC($a_list[$keys[$k]]->rc); ?>">
       <a href="/dashboard/i/<?php echo $a_list[$keys[$k]]->id; ?>"><?php echo $a_list[$keys[$k]]; ?></a>
     </td>
     <?php
       $k++;
       if ($k >= $total) {
         echo "</tr>";
         break 2;
       }
      }
      ?>
    </tr>
  <?php } ?>
	  </tbody>
	  </table>
        </div>
      </div>
