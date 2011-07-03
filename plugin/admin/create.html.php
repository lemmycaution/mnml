<?if(VERB=="POST"){
	if($data){
	?>
	<p class="success">Row successfuly created</p>
	<?	
	}else{
	?>
	<p class="error">Row can't be created</p>	
	<?	
	}
}else{?>

<th>
<form id="create" name="create" action="<?=url_for("admin/create?table=$table")?>" style="width:728px">
	<h2>Creating <?=Inflector::classify($table)?></h2>
	<fieldset>
		<legend>Language</legend>
		<div>
			<? foreach(I18n::$languages as $lang){ ?>
				<button id="lang_<?=$lang?>" class="langbtn" value="_<?=$lang?>" ><?=$lang?></button>
			<? } ?>
		</div>	
	</fieldset>
	<fieldset>
		<legend>Attributes</legend>
<?php
$attributes = $model->attributes_info();
foreach ($attributes as $attr) {
	if(strpos($attr["name"],"id")===false && $attr["name"]!="created_at" && $attr["name"]!="updated_at" ){
?>
	<div>
		<?=admin_tag_label_and_input($attr["name"],$attr["type"],"",$model->is_required($attr["name"]));?>
	</div>	
<?}}?>
	<div>
	</fieldset>
	<fieldset>
		<legend>Relations</legend>
		<? foreach( $model->_belongs_to as $rel=>$opts ){
			if(is_numeric($rel)) $rel=$opts;
			?>
		<div>
			<?=admin_tag_label_and_input_rel($rel,$opts,'belongs_to');?>
		</div>
		<? }?>
		<? foreach( $model->_has_one as $rel=>$opts ){
			if(is_numeric($rel)) $rel=$opts;
			?>
		<div>
			<?=admin_tag_label_and_input_rel($rel,$opts,'has_one');?>
		</div>
		<? }?>
		<? foreach( $model->_has_many as $rel=>$opts ){
			if(is_numeric($rel)) $rel=$opts;
			?>
		<div>
			<?=admin_tag_label_and_input_rel($rel,$opts,'has_many');?>
		</div>
		<? }?>
		<? foreach( $model->_has_and_belongs_to_many as $rel=>$opts ){
			if(is_numeric($rel)) $rel=$opts;			
			?>
		<div>
			<?=admin_tag_label_and_input_rel($rel,$opts,'has_and_belongs_to_many');?>
		</div>
		<? }?>				
	</fieldset>	
	<div class="submit"><button type="submit">Submit</button></div>	
</form>
</th>
<script>
$("form").validate({submitHandler: function(form) {
   	$.fancybox.showActivity();
	$.post(
		$("#create").attr("action"),
		$("#create").serialize(),
		function(data){
			$('body').scrollTop(0);			
			$("th").parent().html(data);
			$.fancybox.hideActivity();			
			setTimeout(function(){location.reload();},1000);			
		}
	);
	return false;
 }});
var lang='_<?=LANG?>';
var langs=[];
$(".langbtn").each(function(){
	langs.push($(this).val());
});

$(".langbtn").click(function(){
	lang=$(this).val();
	toggleLang();
	return false;
})
function toggleLang(){
	$("input , textarea").each(function(){
		for( var i=0; i<langs.length;i++){
			if( $(this).attr("name").indexOf( langs[i] )>-1 ){
				if(langs[i]!=lang)
					$("label[for="+$(this).attr("name")+"]").parent().hide();
				else
					$("label[for="+$(this).attr("name")+"]").parent().show();
			}
		}
	});
}

admin_onPopupFunctions.push(toggleLang);
/*
$("#create").submit(function(){
	$.fancybox.showActivity();
	$.post(
		$("#create").attr("action"),
		$("#create").serialize(),
		function(data){
			$('body').scrollTop(0);			
			$("th").parent().html(data);
			$.fancybox.hideActivity();			
			setTimeout(function(){location.reload();},1000);			
		}
	);
	return false;
});
*/
$.easy.popup();

</script>
<?}?>
