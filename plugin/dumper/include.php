<?
function dumper_dump_sql($connection,$tables){
	echo "/*\n";
	echo "MNML Dump\n";
	echo "Database: ".get_class($connection->database)."\n";
	echo "Date: ".now()."\n";
	echo "*/\n";
	
	foreach($tables as $table){
		dumper_table_structure_sql($connection,$table);
		if($_POST['sql_options']=="structure_data"){
			dumper_table_data_sql($connection,$table);
		}	
	}
}
function dumper_table_structure_sql($connection,$table){
	echo "\n";	
	echo "-- ----------------------------\n";
	echo "--  Table structure for $table\n";
	echo "-- ----------------------------\n";
	echo "DROP TABLE IF EXISTS $table;\n";
	switch( get_class($connection->database) ){
		case "_mysqli":
			dumper_table_structure_mysql($connection,$table);
		break;
		case "_sqlite":
			dumper_table_structure_sqlite($connection,$table);		
		break;
	}
}
function dumper_table_structure_mysql($connection,$table){
	$r = $connection->query("SHOW CREATE TABLE $table;");
	if($r) echo $r[0]['Create Table'].";\n";
	else "-- There is no table named $table\n";
}
function dumper_table_structure_sqlite($connection,$table){
	$r = $connection->query("select * from sqlite_master");
	foreach($r as $t){
		if($t['name']==$table){
			echo $t['sql'].";\n";
			break;
		}
	}
}
function dumper_table_data_sql($connection,$table){
	$nonqm = array("binary","boolean","decimal","float","integer","time","timestamp");
	$columns = $connection->columns($table);
	
	$data = $connection->query("SELECT * FROM $table;");
	echo "\n";
	echo "-- ----------------------------\n";
	echo "--  Records of \"$table\"\n";
	echo "-- ----------------------------\n";
	if($data){
	echo "BEGIN;\n";
	foreach( $data as $row ){
		$colnames=array();
		$colvalues=array();
		foreach($columns as $col){
			$colnames[]=$col['name'];
			$colvalues[]=(!in_array($col['type'],$nonqm))?"'".str_replace("'","''",$row[$col['name']])."'":$row[$col['name']];
		}
		echo "INSERT INTO $table ( ".implode(", ",$colnames)." ) VALUES ( ".implode(", ",$colvalues)." );\n";		
	}	
	echo "COMMIT;\n";
	}else echo "-- No data in table $table\n";
}
function dumper_dump_csv($connection,$tables){
	foreach($tables as $table){
		if($_POST['table']==$table){
			dumper_table_data_csv($connection,$table);
		}	
	}
}
function dumper_table_data_csv($connection,$table){
	$delimiter= $delimiter= $_POST['demiliter'];
	if( 'tab' == $delimiter) $delimiter="\t";
	$nonqm = array("binary","boolean","decimal","float","integer","time","timestamp");
	$columns = $connection->columns($table);
	$data = $connection->query("SELECT * FROM $table;");
	if($data){
		foreach( $data as $row ){
			$colvalues=array();
			foreach($columns as $col){
				$colvalues[]=(!in_array($col['type'],$nonqm))?"'".str_replace("'","''",$row[$col['name']])."'":$row[$col['name']];
			}
			echo implode("$delimiter ",$colvalues)."\n";		
		}
	}	
}
?>