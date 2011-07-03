<div class="main">
<h2>Search results for <?=$_GET["term"]?> ( <?=$count?> )
<?
foreach($data as $result){
	$table = $result["table"];
	$model = $result["model"];
	$list = $result["list"];
	$atts = $model->attributes_info();
	$table_display_name = str_replace(Admin::$table_prefix,"",$table);		
?>	
<h3>Table : <?=tag_a( $table_display_name, url_for("admin/index?order=id&page=0&limit=10&table=$table_display_name" ) );?></h3>
<table cellpadding="0" cellspacing="0">
	<caption>
		<?=tag_a("Create",url_for("admin/create?table=".$table),"fancybox")?> | columns : <?=implode(",",$model->columns());?>
	</caption>	
	<thead>
		<tr>
			<? foreach( $atts as $column){ ?>
			<td><?=$column['name']?></td>
			<? } ?>
			<td></td>			
			<td></td>
			<td>total: <?=$count?></td>		
		</tr>
	</thead>
	<tfoot>
		<tr>
			<? foreach( $atts as $column){ ?>
			<td><?=$column['name']?></td>
			<? } ?>
			<td></td>			
			<td></td>
			<td>total: <?=$count?></td>
		</tr>
	</tfoot>	
	<tbody>
		<?if( empty( $list ) ){?>
		<?}else{
			foreach($list as $row){
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
				<td class="action_link"><?=tag_a("Retrive",url_for( "admin/retrive/".$row['id']."?table=".$table ), "fancybox" )?></td>
				<td class="action_link"><?=tag_a("Update",url_for( "admin/update/".$row['id']."?table=".$table ), "fancybox" )?></td>
				<td class="action_link"><?=tag_a("Destroy",url_for( "admin/destroy/".$row['id']."?table=".$table ), "fancybox" )?></td>		
			</tr>	
		<?}}?>	
	</tbody>
</table>
<?}?>	
</div>