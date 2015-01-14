<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo Setting::get('general', 'sitename')->value; ?> - <?php echo $page['title']; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Espix Network SPRL">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
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
  <body role="document">
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
	  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?php echo Setting::get('general', 'sitename')->value; ?></a>
        </div>
	<div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li class="active"><a href="/index">Home</a></li>
              <li class="dropdown"> 
                <a href="#" class="dropdown-toggle" role="button" aria-expanded="false" data-toggle="dropdown">Informations <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li class="dropdown-header">Servers</li>
                  <li><a href="/list/w/server">List</a></li>
                  <li><a href="/list/w/sgroup">List Groups</a></li>
                  <li><a href="/add/w/server">Add</a></li>
                  <li><a href="/add/w/sgroup">Add Group</a></li>
		  <li>
		    <form role="form" action="/search/w/server" method="POST">
		      <input name="q" type="text" class="form-control input-sm" placeholder="Search Server">
	    	    </form>
		  </li>
                  <li class="divider"></li>
                  <li class="dropdown-header">Virtual Machines</li>
                  <li><a href="/list/w/vm">List</a></li>
		  <li>
		    <form role="form" action="/search/w/vm" method="POST">
		      <input name="q" type="text" class="form-control input-sm" placeholder="Search VM">
	    	    </form>
		  </li>
                  <li class="divider"></li>
                  <li class="dropdown-header">Physical</li>
                  <li><a href="/list/w/pserver">List</a></li>
                  <li><a href="/add/w/pserver">Add</a></li>
		  <li>
		    <form role="form" method="POST" action="/search/w/pserver">
		      <input name="q" type="text" class="form-control input-sm" placeholder="Search Physical">
	    	    </form>
		  </li>
                  <li class="divider"></li>
                  <li class="dropdown-header">Clusters</li>
                  <li><a href="/list/w/cluster">List</a></li>
                  <li><a href="/add/w/cluster">Add</a></li>
		  <li>
		    <form role="form" method="POST" action="/search/w/cluster">
		      <input name="q" type="text" class="form-control input-sm" placeholder="Search Cluster">
	    	    </form>
		  </li>
                </ul>
              </li> 
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Checks <b class="caret"></b></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="/dashboard">Dashboard</a></li>
                  <li><a href="/list/w/results">Results</a></li>
                  <li><a href="/list/w/check">List</a></li>
                  <li><a href="/add/w/check">Add</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools <b class="caret"></b></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="/list/w/jobs#">Jobs list</a></li>
                  <li><a href="/tools/w/cdp">CDP Packets</a></li>
                  <li><a href="/rrdlive">RRD Live</a></li>
                  <li><a href="/tools/w/stats">Statistics</a></li>
                  <li><a href="/tools/w/rce">Remote Code Execution</a></li>
<?php foreach(Plugin::getWebLinks('tools') as $l) { ?>
                  <li><a href="<?php echo $l->getHref(); ?>"><?php echo $l->desc; ?></a></li>
<?php } ?>
                </ul>
              </li>
<?php /* list non-default cat from plugins... */
      foreach(Plugin::getWebCat() as $cat) {
        $links = Plugin::getWebLinks($cat);
        $name = '';
        if (count($links) > 0) { /* at least one element */
          $name = $links[0]->cat;
        } else {
          continue;
        }
?>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $name; ?> <b class="caret"></b></a>
                <ul class="dropdown-menu" role="menu">
<?php    foreach($links as $l) { ?>
                  <li><a href="<?php echo $l->getHref(); ?>"><?php echo $l->desc; ?></a></li>
<?php    } ?>
                </ul>
              </li>
<?php } ?>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Settings <b class="caret"></b></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="/view/w/login/i/self">My Profile</a></li>
                  <li><a href="/settings">Configuration</a></li>
                  <li><a href="/list/w/rjob">Job Crontab</a></li>
                  <li><a href="/list/w/login#">Users management</a></li>
                  <li><a href="/list/w/ugroup#">Groups management</a></li>
                  <li><a href="/list/w/susers#">Connect Users management</a></li>
                  <li><a href="/list/w/pid">Show Daemons</a></li>
<?php foreach(Plugin::getWebLinks('settings') as $l) { ?>
                  <li><a href="<?php echo $l->getHref(); ?>"><?php echo $l->desc; ?></a></li>
<?php } ?>
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

    <div role="main" class="container">
