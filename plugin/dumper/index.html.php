<th>
	<form action="<?=url_for("dumper/dump")?>" method="POST">
		<div>
			<label for="table">Table Name:</label>
			<select name="table">
				<option value="all">All Tables</option>
				<? foreach($tables as $table){?>
				<option value="<?=$table?>"><?=$table?></option>			
				<? }?>
			</select>				
		</div>
		<div>
		<label for="type">Type:</label>	
		<select name="type">
			<option value="sql">SQL</option>
			<option value="csv">CSV</option>			
		</select>	
		</div>
		<fieldset id="sql">
			<legend>SQL Options</legend>
			<div>
				<select name="sql_options">
					<option value="structure">Structure Only</option>
					<option value="structure_data">Structure and Data</option>			
				</select>
			</div>	
		</fieldset>	
		<fieldset id="csv">
			<legend>CSV Options</legend>
			<div>
				<label for="type">Delimiter:</label>	
				<select name="demiliter">
					<option value=",">Comma (,)</option>
					<option value="|">Pipe (|)</option>
					<option value="tab">Tab (	)</option>
				</select>
			</div>	
		</fieldset>
		<div id="submit"><button class="submit">Dump</button></div>
	</form>	
</th>
<script type="text/javascript">
admin_onPopupFunctions.push(function(){
	$("select[name=type]").change(function(){
		$("#"+$(this).val()).show();
		$("#"+($(this).val()=="csv"?"sql":"csv")).hide();
	});
	$("#csv").hide();
});
</script>