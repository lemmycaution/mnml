<!DOCTYPE html> 
<html lang="en"> 
<head> 
	<title>FileManager Plugin</title> 
	<meta name="description" content="Description">  	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 

	<link rel="stylesheet" href="<?=url_for("js/lib/easy/css/easy.css")?>" media="screen" /> 
	<link rel="stylesheet" href="<?=url_for("js/lib/easy/css/easyprint.css")?>" media="print" /> 	
	<link rel="stylesheet" href="<?=url_for("js/plugin/jquery.uploadify/css/default.css")?>" media="screen" />
	<link rel="stylesheet" href="<?=url_for("js/plugin/jquery.uploadify/css/uploadify.css")?>" media="screen" />
	<link rel="stylesheet" href="<?=url_for("js/plugin/jquery.imgareaselect/css/imgareaselect-animated.css")?>" media="screen" />	
	<style>
	#container{width:99% !important;}
	ol.pagination{
		margin:1em 0;
		padding:0;
		}	
	ol.pagination li{
		margin:0 .5em 0 0;
		padding:0;
		float:left;
		list-style:none;
		}
	ol.pagination li a, ol.pagination li span{
		float:left;
		border:1px solid #ccc;
		line-height:1.5em;
		padding:0 .5em;
		background:#fff;
		}
	ol.pagination li a:hover{background:#f1f1f1;}
	ol.pagination li span{background:#ccc;color:#fff;}
	.secondary input{
		width:268px;
	}
	#img_to_crop{
		border:1px dotted #ff0000;
	}
	</style>
	
	<script type="text/javascript" src="<?=url_for("js/lib/jquery-1.5.1.min.js")?>"></script>
	
	<script type="text/javascript" src="<?=url_for("js/lib/easy/js/easy.js")?>"></script>
	<script type="text/javascript" src="<?=url_for("js/lib/easy/js/main.js")?>"></script>	
	
	<script type="text/javascript" src="<?=url_for("js/plugin/jquery.uploadify/js/swfobject.js")?>"></script>
	<script type="text/javascript" src="<?=url_for("js/plugin/jquery.uploadify/js/jquery.uploadify.v2.1.0.min.js")?>"></script>
	<script type="text/javascript" src="<?=url_for("js/plugin/jquery.imgareaselect/scripts/jquery.imgareaselect.pack.js")?>"></script>

	<script>
	$(document).ready(function(){
		$("a.external").attr("target","_blank");
		$("a.insert").click(function (){
			if(window.parent.file_manager_attach_source){
				window.parent.file_manager_attach_source.val( '<?=url_for("upload/")?>'+$(this).attr("rel") );
				window.parent.file_manager_attach_source=null;				
				file_manager_close_popup();
			}
		});
		
		<? if( has_plugin("tiny_mce") && isset($_GET['attach']) ) {?>
			if(window.tiny_mce_update_insert_function)
				tiny_mce_update_insert_function();
			else if(window.parent.tiny_mce_update_insert_function)
				window.parent.tiny_mce_update_insert_function();
		<? }?>
		
		$("#search").keypress(function(event) {
			if(event.keyCode==13)
				location.href="<?=url_for("file_manager/search?term=")?>"+$("#search").val();
		});
	});
	function file_manager_close_popup(){
		$('#easy_popup', window.parent.document).remove();
		$('#easy_popupcontent', window.parent.document).remove();
	}
	</script>
</head> 
<body> 
	<div id="container">
		<div id="header">
			<h1>Framework | File Manager Plugin</h1>
			<ul id="nav">
				<?
					$attach = (isset($_GET['attach'])) ? "?attach=true" : "";
				?>
				<li><?=tag_a("Index",url_for("file_manager/index".$attach));?></li>
				<li><?=tag_a("Upload",url_for("file_manager/upload".$attach));?></li>				
				<li><input type="text" name="search" id="search" /></li>
			</ul>
		</div>	

		<div class="content">
		<?
		flash();
		$this->render_view( "plugin", $view );
		?>
		</div>
		
		<div id="footer">
		</div>	
	</div>
</body> 
</html>