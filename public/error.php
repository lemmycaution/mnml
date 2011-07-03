<!DOCTYPE html> 
<html lang="en"> 
<head> 
	<meta charset="utf-8">

	<title>Error: <?=$code?></title>
	<meta name="description" content="" />
	<meta name="author" content="" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
	<link rel="shortcut icon" href="/favicon.ico" />
	
	<style>
	#error{margin:10px;font-family:Arial;}
	#error > h1{border:10px solid #ff0066;background:#ffffff;font-size:3em;color:#ff0000;padding:15px;}
	#error > p{padding:15px;background:#f0f0f0;margin-top:10px;}
	#error > pre{font-family:Courier;font-size:.8em;padding:10px;}
	</style>
</head> 
<body>
	<div id="error"> 
	<h1>Error: <?=$code?></h1>
	<p>
		request : <?=$_SERVER["REQUEST_URI"]?>
	</p>	
	<? if( isset($e) && ENVIRONMENT!="production"){
		echo "<pre>";
		print_r( $e );
		echo "</pre>";
	} ?>	
	</div>
</body> 
</html>
