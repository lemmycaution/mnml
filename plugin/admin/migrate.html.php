<? if ( isset($_GET['approve']) ) {?>
<p class="success">Migration successfully done</p>
<script>
setTimeout(function(){location.reload();},1000);
</script>
<? } else { ?>
<p class="error">Are you sure to migrate <?=$_GET['class_name']?></p>	
<?=tag_a("Okay",url_for( "admin/migrate/".ID."?approve=true&class_name=".$_GET['class_name'] ), "fancybox" )?>
<script>
$(".fancybox").fancybox({'titleShow':false});
</script>
<? }?>