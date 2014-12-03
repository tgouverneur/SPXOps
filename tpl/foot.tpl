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
      <footer class="col-md-offset-1">&copy; 2012-2014 <a href="http://espix.net">Espix Network SPRL</a> - <a href="https://github.com/tgouverneur/SPXOps/wiki">SPXOps</a> vDEVEL</footer>
    </div> <!-- /container -->
<?php if (isset($js)) { foreach($js as $j) { ?>
    <script src="/js/<?php echo $j; ?>"></script>
<?php } } ?>
  </body>
</html>
