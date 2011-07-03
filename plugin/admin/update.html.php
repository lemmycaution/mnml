<th>
<?if(VERB=="POST"){
	if($data){
	?>
	<p class="success">Row successfuly updated</p>
	<script>
	$("#row_<?=ID?>").css("background","#33cc00");	
	</script>
	<?	
	}else{
	?>
	<p class="error">Row can't be updated</p>	
	<?	
	}
}
?>	
<h2>Updating <?=Inflector::classify($table)?> <?=ID?></h2>
<form id="update" name="update" action="<?=url_for("admin/update/".ID."?table=$table")?>">
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
	$value = $attr["name"]=="password" ? "" : is_array($data) ? $data[$attr["name"]] : $data->$attr["name"];
	if(strpos($attr["name"],"id")===false && $attr["name"]!="created_at" && $attr["name"]!="updated_at" ){
?>
	<div>
		<?=admin_tag_label_and_input($attr["name"],$attr["type"],$value,$model->is_required($attr["name"]));?>
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
			<?=admin_tag_label_and_input_rel($rel,$opts,'belongs_to',$model->$rel);?>
		</div>
		<? }?>
		<? foreach( $model->_has_one as $rel=>$opts ){
			if(is_numeric($rel)) $rel=$opts;
			?>
		<div>
			<?=admin_tag_label_and_input_rel($rel,$opts,'has_one',$model->$rel);?>
		</div>
		<? }?>
		<? foreach( $model->_has_many as $rel=>$opts ){
			if(is_numeric($rel)) $rel=$opts;
			?>
		<div>
			<?=admin_tag_label_and_input_rel($rel,$opts,'has_many',$model->$rel);?>
		</div>
		<? }?>
		<? foreach( $model->_has_and_belongs_to_many as $rel=>$opts ){
			if(is_numeric($rel)) $rel=$opts;			
			?>
		<div>
			<?=admin_tag_label_and_input_rel($rel,$opts,'has_and_belongs_to_many',$model->$rel);?>
		</div>
		<? }?>				
	</fieldset>

	<div class="submit"><button type="submit">Submit</button></div>	
</form>
</th>
<script>
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

$("#update").submit(function(){
	$.fancybox.showActivity();
	$.post(
		$("#update").attr("action"),
		$("#update").serialize(),
		function(data){
			$('body').scrollTop(0);			
			$("th").parent().html(data);
			$.fancybox.hideActivity();
			<? if ( has_plugin( "tiny_mce" ) ) { ?>
				tiny_mce_init_tiny_mce();
			<? } ?>	
			toggleLang();
			$("select[multiple]").multiselect();
			$( ".datetime" ).datepicker({'dateFormat':'yy-mm-dd'});
		}
	);
	return false;
});

$.easy.popup();
</script>
