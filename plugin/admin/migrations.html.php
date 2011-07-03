<div class="main">
<h2>Migrations</h2>
<table cellpadding="0" cellspacing="0">
	<caption>
		You can create or drop tables from here, be carefull!
	</caption>	
	<thead>
		<tr>
			<td>table name</td>
			<td></td>			
			<td></td>	
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td>total</td>			
			<td></td>
			<td><?=$count?></td>
		</tr>
	</tfoot>	
	<tbody>
		<?if( empty( $migrations ) ){?>
		<?}else{
			foreach($migrations as $migration){
				$file_name = str_replace(DB_PATH."migrations".DIRECTORY_SEPARATOR,"",$migration);
				$table_name = Inflector::tableize(str_replace("_migration.php","",$file_name));
				$migration_class_name = Inflector::classify(str_replace(".php","",$file_name));
			?>
			<tr id="migration_<?=$table_name?>">
				<td><?=$table_name?></td>
				<td><?=tag_a("Up",url_for( "admin/migrate/up"."?class_name=".$migration_class_name ), "fancybox" )?></td>
				<td><?=tag_a("Down",url_for( "admin/migrate/down"."?class_name=".$migration_class_name ), "fancybox" )?></td>
			</tr>	
		<?}}?>	
	</tbody>
</table>
</div>