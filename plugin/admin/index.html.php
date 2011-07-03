<? $atts = $model->attributes_info();?>
<div class="main">
<div>
	<? foreach(I18n::$languages as $lang){ ?>
		<button id="lang_<?=$lang?>" class="langbtn" value="_<?=$lang?>" ><?=$lang?></button>
	<? } ?>
</div>		
<h2>Table : <?=$table?></h2>
<table cellpadding="0" cellspacing="0">
	<caption>
		<?=tag_a("Create",url_for("admin/create?table=".$table),"fancybox")?> | columns : <?=implode(",",$model->columns());?>
	</caption>	
	<thead>
		<tr>
			<? 
			$_order=$order;
			foreach( $atts as $column){ 
				$_order = strpos($_GET['order'],"DESC")===false ? $column['name']." DESC" : $column['name'];
			?>
			<td class="<?=$column['name']?>"><?=tag_a($column['name'],url_for("admin/index?order=$_order&page=$page&limit=$limit&table=$table"))?></td>
			<? } ?>
			<td></td>			
			<td></td>
			<td>total: <?=$count?></td>		
		</tr>
	</thead>
	<tfoot>
		<tr>
			<? 
			$_order=$order;
			foreach( $atts as $column){ 
				$_order = strpos($_GET['order'],"DESC")===false ? $column['name']." DESC" : $column['name'];
			?>
			<td class="<?=$column['name']?>"><?=tag_a($column['name'],url_for("admin/index?order=$order&page=$page&limit=$limit&table=$table"))?></td>
			<? } ?>
			<td></td>			
			<td></td>
			<td>total: <?=$count?></td>			
		</tr>
	</tfoot>	
	<tbody>
		<?if( empty( $data ) ){?>
		<?}else{
			foreach($data as $row){
			?>
			<tr id="row_<?=$row['id']?>">
				<? 
				foreach( $atts as $column){ 
				?>
				<td class="<?=$column['name']?>">
					<?
						if($column['type']=="richtext")
							echo "<code>".substr(parserichtext($row[$column['name']]),0,50)."</code>";
						else
							echo substr(($row[$column['name']]),0,50);						
					?>
				</td>
				<? } ?>
				<td class="action_link retrive"><?=tag_a("Retrive",url_for( "admin/retrive/".$row['id']."?table=".$table ), "fancybox" )?></td>
				<td class="action_link update"><?=tag_a("Update",url_for( "admin/update/".$row['id']."?table=".$table ), "fancybox" )?></td>
				<td class="action_link destroy"><?=tag_a("Destroy",url_for( "admin/destroy/".$row['id']."?table=".$table ), "fancybox" )?></td>		
			</tr>	
		<?}}?>	
	</tbody>
</table>
<ol class="pagination fixed">
	<?
	$page_prev=($page>0)?$page-1:0;
	$page_next=($page<floor($count/$limit))?$page+1:floor($count/$limit);
	?>
	<li><?=tag_a("&lt;",url_for("admin/index?order=$order&page=$page_prev&limit=$limit&table=$table"));?></li>
	<?for($i=0;$i<ceil($count/$limit);$i++){?>
	<li><?=tag_a($i,url_for("admin/index?order=$order&page=$i&limit=$limit&table=$table"));?></li>
	<?}?>	
	<li><?=tag_a("&gt;",url_for("admin/index?order=$order&page=$page_next&limit=$limit&table=$table"));?></li>
</ol>		
</div>
<script>

$(document).ready(function(){
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
toggleLang();
function toggleLang(){
	$("td").each(function(){
		for( var i=0; i<langs.length;i++){
			if( $(this).attr("class").indexOf( langs[i] )>-1 ){
				if(langs[i]!=lang)
					$(this).hide();
				else
					$(this).show();
			}
		}
	});
}

<? if( isset($_GET['action_id']) && isset($_GET['action']) ):?>
	$("#row_<?=$_GET['action_id']?> .<?=$_GET['action']?> a").click();
<? endif;?>
});



</script>