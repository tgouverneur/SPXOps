<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo Setting::get('general', 'sitename')->value; ?> - <?php echo $page['title']; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Espix Network SPRL">
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link href="/css/spxops.css" rel="stylesheet">
<?php if (isset($css)) { foreach($css as $j) { ?>
    <link href="/css/<?php echo $j; ?>" rel="stylesheet">
<?php } } ?>
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
    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
<?php if (isset($js)) { foreach($js as $j) { ?>
    <script src="/js/<?php echo $j; ?>"></script>
<?php } } ?>
<?php if (isset($head_code)) { echo $head_code; } ?>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#"><?php echo Setting::get('general', 'sitename')->value; ?></a>
            <ul class="nav">
              <li class="active"><a href="/index">Home</a></li>
              <li class="dropdown"> 
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Informations <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li class="nav-header">Servers</li>
                  <li><a href="/list/w/server">List</a></li>
                  <li><a href="/list/w/sgroup">List Groups</a></li>
                  <li><a href="/add/w/server">Add</a></li>
                  <li><a href="/add/w/sgroup">Add Group</a></li>
		  <li>
		    <form class="navbar-search pull-left" action="/search/w/server" method="POST">
		      <input name="q" type="text" class="search-query" placeholder="Search Server">
	    	    </form>
		  </li>
                  <li class="divider"></li>
                  <li class="nav-header">Virtual Machines</li>
                  <li><a href="/list/w/vm">List</a></li>
		  <li>
		    <form class="navbar-search pull-left" action="/search/w/vm" method="POST">
		      <input name="q" type="text" class="search-query" placeholder="Search VM">
	    	    </form>
		  </li>
                  <li class="divider"></li>
                  <li class="nav-header">Physical</li>
                  <li><a href="/list/w/pserver">List</a></li>
                  <li><a href="/add/w/pserver">Add</a></li>
		  <li>
		    <form class="navbar-search pull-left" method="POST" action="/search/w/pserver">
		      <input name="q" type="text" class="search-query" placeholder="Search Physical">
	    	    </form>
		  </li>
                  <li class="divider"></li>
                  <li class="nav-header">Clusters</li>
                  <li><a href="/list/w/cluster">List</a></li>
                  <li><a href="/add/w/cluster">Add</a></li>
		  <li>
		    <form class="navbar-search pull-left" method="POST" action="/search/w/cluster">
		      <input name="q" type="text" class="search-query" placeholder="Search Cluster">
	    	    </form>
		  </li>
                </ul>
              </li> 
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Checks <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/dashboard">Dashboard</a></li>
                  <li><a href="/list/w/results">Results</a></li>
                  <li><a href="/list/w/check">List</a></li>
                  <li><a href="/add/w/check">Add</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/list/w/jobs#">Jobs list</a></li>
                  <li><a href="/tools/w/cdp">CDP Packets</a></li>
                  <li><a href="/tools/w/stats">Statistics</a></li>
                  <li><a href="/tools/w/rce">Remote Code Execution</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Settings <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/view/w/login/i/self">My Profile</a></li>
                  <li><a href="/settings">Configuration</a></li>
                  <li><a href="/list/w/rjob">Job Crontab</a></li>
                  <li><a href="/list/w/login#">Users management</a></li>
                  <li><a href="/list/w/ugroup#">Groups management</a></li>
                  <li><a href="/list/w/susers#">Connect Users management</a></li>
                  <li><a href="/list/w/pid">Show Daemons</a></li>
                </ul>
              </li>
              <li><a href="/about">About</a></li>
            </ul>
<?php if (isset($page['login'])) { ?>
	    <p class="navbar-text pull-right">Welcome <?php echo $page['login']->fullname; ?> ! (<a href="/logout">logout</a>)</p>
<?php } else { ?>
	    <p class="navbar-text pull-right">Not Logged-in. (<a href="/login">login</a>)</p>
<?php } ?>
        </div>
      </div>
    </div>

    <div class="container">
