<?
header("Content-type: application/xhtml+xml; charset=utf-8");
echo '<?xml version="1.0" encoding="utf-8" ?>';
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN"
"http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Cache-Control" content="max-age=200; no-transform" />

	<title></title>
	<meta name="description" content="" />
	<meta name="author" content="" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
</head>
<body> 
	<? $this->render_view( $layout, $view ); ?>
</body>
</html>