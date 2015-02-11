<?php if (isset($a_link)) { ?>
        <div class="page-header">
          <h1>Links</h1>
        </div>
        <ul class="nav nav-pills nav-stacked">
<?php foreach($a_link as $link) { ?>
          <li class="col-md-4" role="presentation"><a href="<?php echo $link['href']; ?>"><?php echo $link['name']; ?></a></li>
<?php } ?>
        </ul>
<?php } ?>
      <hr>
<?php
 if (Config::$webgui_time) {
   $stop_time = microtime();
   $stop_time = explode(' ',$stop_time);
   $stop_time = $stop_time[1] + $stop_time[0];
   $dur_time = ' - '.(round($stop_time - Config::start_time, 2)).' seconds to load';
 } else {
   $dur_time ='';
 }
?>
      <footer class="col-md-offset-1">&copy; 2012-2015 <a href="http://espix.net">Espix Network SPRL</a> - <a href="https://github.com/tgouverneur/SPXOps/wiki">SPXOps</a> vDEVEL<?php echo $dur_time; ?></footer>
    </div> <!-- /container -->
<?php if (isset($js)) { foreach($js as $j) { ?>
    <script src="/js/<?php echo $j; ?>"></script>
<?php } } ?>
  </body>
</html>
