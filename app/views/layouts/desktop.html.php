<!DOCTYPE html> 
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="<?=LANG?>"> <!--<![endif]--> 
<head> 
	<meta charset="utf-8">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">	

	<title></title>
	<meta name="description" content="" />
	<meta name="author" content="" />
	
	<meta property="og:title" content=""/>
	<meta property="og:type" content=""/>
	<meta property="og:url" content=""/>
	<meta property="og:image" content=""/>
	<meta property="og:site_name" content=""/>
	<meta property="fb:admins" content="USER_ID"/>
	<meta property="og:description" content=""/>
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
	<link rel="shortcut icon" href="/favicon.ico" />
  	<link rel="apple-touch-icon" href="/apple-touch-icon.png">	

	<link rel="stylesheet" href="/css/desktop.css?v=1" /> 
	<script src="/js/libs/modernizr-1.7.min.js"></script>	
</head> 
<body> 
	<div id="container">
	    <header>

	    </header>
	    <div id="main" role="main">
			<? $this->render_view( $layout, $view ); ?>
	    </div>
	    <footer>

	    </footer>
	  </div>
	
	<script src="/js/libs/jquery-1.5.1.min.js"></script>

	<!--[if lt IE 7 ]>
    <script src="/js/libs/dd_belatedpng.js"></script>
    <script>DD_belatedPNG.fix("img, .png_bg");</script>
	<![endif]-->
	
	<script>
	var _gaq=[["_setAccount","UA-XXXXX-X"],["_trackPageview"]]; // Change UA-XXXXX-X to be your site's ID 
	(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
	g.src=("https:"==location.protocol?"//ssl":"//www")+".google-analytics.com/ga.js";
	s.parentNode.insertBefore(g,s)}(document,"script"));
	</script>
</body> 
</html>