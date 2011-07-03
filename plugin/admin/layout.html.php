<!DOCTYPE html> 
<html lang="en"> 
<head> 
	<title>Admin Plugin</title> 
	<meta name="description" content="Description">  	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 

	<link rel="stylesheet" href="<?=url_for("js/lib/easy/css/easy.css")?>" media="screen" /> 
	<link rel="stylesheet" href="<?=url_for("js/lib/easy/css/easyprint.css")?>" media="print" /> 	
	<link rel="stylesheet" href="<?=url_for("js/lib/jquery.ui/css/flick/jquery-ui-1.8.10.custom.css")?>" media="screen" /> 		
	<link rel="stylesheet" href="<?=url_for("js/plugin/multiselect/css/ui.multiselect.css")?>" media="screen" />	
	<link rel="stylesheet" href="<?=url_for("js/plugin/fancybox/jquery.fancybox-1.3.2.css")?>" media="screen" /> 		
	<style>
	body{
		font-size:70%;
	}
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
	.created_at, .updated_at{
		min-width:65px;
		max-width:65px;
	}
	#container{
		width:95%;
		min-width:920px;
	}
	.main{
		width:100%;
		min-width:600px;
	}
	.action_link{
		max-width:32px;
	}
	</style>
	<script type="text/javascript" src="<?=url_for("js/lib/jquery-1.5.1.min.js")?>"></script>
	<script type="text/javascript" src="<?=url_for("js/lib/jquery.ui/js/jquery-ui-1.8.10.min.js")?>"></script>	
	
	<script type="text/javascript" src="<?=url_for("js/lib/easy/js/easy.js")?>"></script>
	<script type="text/javascript" src="<?=url_for("js/lib/easy/js/main.js")?>"></script>	
	
	<script type="text/javascript" src="<?=url_for("js/plugin/fancybox/jquery.fancybox-1.3.2.pack.js")?>"></script>

	<script type="text/javascript" src="<?=url_for("js/plugin/multiselect/js/ui.multiselect.js")?>"></script>
	<script type="text/javascript" src="<?=url_for("js/plugin/jquery.validate.js")?>"></script>
	
	<script type="text/javascript">	
	var admin_onFormFunctions = [];	
	var admin_onPopupFunctions = [];
	var admin_onReadyFunctions = [];	
	function admin_onPopup(){
		
		$(".fancybox").fancybox({'titleShow':false,'onComplete':admin_onPopup});
		//setTimeout(function(){$.fancybox.resize();$.fancybox.center();},1000);
		
		$("select[multiple]").multiselect({
			//remoteUrl: "ajax.php"
		});

		$( "input[class~=date], input[class~=datetime]" ).datepicker({'dateFormat':'yy-mm-dd'});
			
		for (var i=0;i<admin_onPopupFunctions.length;i++)
			admin_onPopupFunctions[i]();
	}
	function admin_onForm(){
		
		$(".fancybox").fancybox({'titleShow':false,'onComplete':admin_onPopup});
		
		for (var i=0;i<admin_onFormFunctions.length;i++)
			admin_onFormFunctions[i]();
	}
	function admin_onReady(){
		
		$(".fancybox").fancybox({'titleShow':false,'onComplete':admin_onPopup});
		
		for (var i=0;i<admin_onReadyFunctions.length;i++)
			admin_onReadyFunctions[i]();
	}
	$().ready(function(){
		
		
		$(".button.create").click(function(){
			if( $('.form.create').data('loaded')==undefined ){
				$('.form.create').load($(this).attr('href'),admin_onForm);
				$('.form.create').data('loaded',true);
			}else{
				$('.form.create').toggle();				
			}
			return false;
		});

		
		$("#search").keypress(function(event) {
			if(event.keyCode==13)
				location.href="<?=url_for("admin/search?term=")?>"+$("#search").val();
		});
		
		admin_onReady();
	});
	</script>
	<? if ( has_plugin( "file_manager" ) ) require_plugin("file_manager") ?>
	<? if ( has_plugin( "tiny_mce" ) ) require_plugin("tiny_mce") ?>
</head> 
<body> 
	<div id="container">
		<div id="header">
			<h1>Mnml | Admin Plugin</h1>
			<ul id="nav">
				<? if( isset($_SESSION["admin_auth"]) ) : ?>
				<li>
					<?=tag_a( "Home", url_for("admin/home") );?>
				</li>	
				<?if ( $_SESSION["admin_auth"]["role"]=="root" ) {?>
				<li>
					<?=tag_a( "Migrations", url_for("admin/migrations") );?>
				</li>	
				<?}?>
				
				<li>
					<?=tag_a( "Models", "#" );?>
					<ul>
					<?
					$tables = $this->connection->tables();
					sort($tables,SORT_STRING);
					foreach($tables as $table){
						$table_display_name = str_replace(Admin::$table_prefix,"",$table);
						if(has_model(strtolower(Inflector::classify($table_display_name)))){
					?>
					<li><?=tag_a( $table_display_name, url_for("admin/index?order=id&page=0&limit=10&table=$table_display_name" ) );?></li>
					<?}}?>
					</ul>
				</li>
				
				<li>
					<?=tag_a( "Plugins", "#" );?>
					<ul>
						<? if( has_plugin("file_manager") ){ ?>
						<li><?=tag_a( "File Manager", url_for("file_manager/index"), "filemanager" )?></li>
						<? } ?>
				
						<? if( has_plugin("dumper") ){ ?>
						<li><?=tag_a( "Dumper" , url_for("dumper/index"), "fancybox" );?></li>
						<? } ?>
					</ul>
				</li>	
				<li><?=tag_a( "Logout" , url_for("admin/logout") );?></li>
				
					<? if( isset($_SESSION["admin_auth"]) ) : ?>
						<!--<div class="secondary">
							<legend>Search</legend>-->
							<li>	
							<input type="text" id="search" value="search" onfocus="this.value=''" />
							</li>
						<!--</div>-->
					<?endif;?>
				
				<? endif;?>
			<ul>		
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