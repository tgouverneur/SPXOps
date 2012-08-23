<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>SPXOps - <?php echo $page['title']; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Espix Network SPRL">
    <link href="/css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="/js/html5.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="/ico/favicon.ico">
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#">SPXOps</a>
            <ul class="nav">
              <li class="active"><a href="#">Home</a></li>
              <li class="dropdown"> 
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Informations <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li class="nav-header">Servers</li>
                  <li><a href="#">List</a></li>
                  <li><a href="#">Add</a></li>
                  <li class="divider"></li>
                  <li class="nav-header">Chassis</li>
                  <li><a href="#">List</a></li>
                  <li><a href="#">Add</a></li>
                  <li class="divider"></li>
                  <li class="nav-header">Clusters</li>
                  <li><a href="#">List</a></li>
                  <li><a href="#">Add</a></li>
                </ul>
              </li> 
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Checks <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">Dashboard</a></li>
                  <li><a href="#">Results</a></li>
                  <li><a href="#">List</a></li>
                  <li><a href="#">Add</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">Jobs list</a></li>
                  <li><a href="#">IDR Patches</a></li>
                  <li><a href="#">NFS Impact</a></li>
                  <li><a href="#">CDP Packets</a></li>
                  <li><a href="#">Remote Code Execution</a></li>
                </ul>
              </li>
              <li><a href="#">About</a></li>
              <li><a href="#">Contact</a></li>
            </ul>
<?php if (isset($lo)) { ?>
	    <p class="navbar-text pull-right">Welcome Thomas Gouverneur !</p>
<?php } else { ?>
	    <p class="navbar-text pull-right">Not logged-in.</p>
<?php } ?>
        </div>
      </div>
    </div>

    <div class="container">
