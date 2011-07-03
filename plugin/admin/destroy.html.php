<?if(VERB=="POST"){
	if($data){
	?>
	<p class="success">Row successfuly deleted</p>
	<script>
	$("#row_<?=ID?>").css("background","#ff0000");
	$("#row_<?=ID?> a").remove();
	//location.reload();
	</script>
	<?	
	}else{
	?>
	<p class="error">Row can't be deleted</p>	
	<?	
	}
}else{?>
<th>
<h2>Destroying <?=Inflector::classify($table)?> <?=ID?></h2>
<h3>Attributes</h3>
<ul>
<?php
$attributes = $model->attributes_info();
foreach ($attributes as $attr) {
	$value = $data[$attr["name"]];
?>
	<li>
	<b><?=$attr["name"]."&lt;<i>".$attr["type"]."</i>&gt;"?></b>: <?=$value?>
	</li>	
<?}?>
</ul>
<div class="submit"><button type="submit" onclick="destroy();">Destroy</button></div>	
</th>
<script>
function destroy(){
	$.fancybox.showActivity();
	$.post(
		"<?=url_for("admin/destroy/".ID."?table=$table")?>",
		{id:<?=ID?>},
		function(data){
			$("th").parent().html(data);
			$.fancybox.hideActivity();			
		}
	);
}
</script>
<?}?>