<?php if (isset($a_link)) { ?>
      <div class="row">
        <div class="row10 offset1">
  <?php foreach($a_link as $l) { ?>
     <a href="<?php echo $l['href']; ?>"><?php echo $l['name']; ?></a><br/>
  <?php } ?>
	</div>
      </div>
<?php } ?>
      <hr>
      <footer>&copy; 2012-2014 <a href="http://espix.net">Espix Network SPRL</a> - <a href="http://spxops.espix.net">SPXOps</a> vDEVEL</footer>
    </div> <!-- /container -->
<?php if (isset($js)) { foreach($js as $j) { ?>
    <script src="/js/<?php echo $j; ?>"></script>
<?php } } ?>
  </body>
</html>
