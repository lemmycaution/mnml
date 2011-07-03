<?
function admin_tag_label_and_input( $name, $type, $value='', $required=null){
	$label = "<label for=\"$name\">$name&lt;<i>$type</i>&gt;</label>";
	$input = admin_tag_input( $name, $type, $value, $required );
	return $label.$input;
}
function admin_tag_input( $name, $type, $value, $required=null ){
	if($name=="file"){
		$input = tag_input( $name, $type, $value, $type.$required );
		return $input;
	}else{
		switch( $type ){
			case "text": case "richtext":
			return tag_textarea( $name, $value, $type.$required );
			break;
			case "boolean":
			return tag_select( 
				$name, array( 
				array("value"=>"0", "label"=>"false"),
				array("value"=>"1", "label"=>"true") 
				), $value );
			break;
			case "datetime":
			return tag_input( $name, "string", $value, $type.$required );			
			break;
			default:
			return tag_input( $name, $type, $value, $type.$required );
			break;		
		}
	}
}
function admin_tag_label_and_input_rel( $name, $opts, $type, $value=''){
	$input='';
	switch( $type ){
		case "belongs_to": 
		$rel_model = Inflector::classify($name);
		$rel_model = new $rel_model();
		$options=array(array("value"=>"", "label"=>"empty"));
		foreach( $rel_model->find('all') as $opt)
			$options[]=array('value'=>$opt->id,'label'=>$opt->{first_string($opt)});
		$name_id = $name==$opts ? $name."_id" : ( isset($opts['foreign_key']) ? $opts['foreign_key'] : $name."_id" ); 	
		$input = tag_select( 
			$name_id, 
			$options, 
			($value) ? $value->id : null );
		break;
		
		case "has_one":
		$rel_model = Inflector::classify($name);
		$rel_model = new $rel_model();
		$options=array(array("value"=>"", "label"=>"empty"));
		foreach( $rel_model->find('all') as $opt)
			$options[]=array('value'=>$opt->id,'label'=>$opt->{first_string($opt)});
		$name_id = $name; 	
		$input = tag_select( 
			$name_id, 
			$options, 
			$value ? $value->id : null );
		break;
		
		case "has_many":case "has_and_belongs_to_many":
		
		$rel_model = Inflector::classify($name);
		$rel_model = new $rel_model();
		//$options=array(array("value"=>"", "label"=>"empty"));
		$options=array();
		foreach( $rel_model->find('all') as $opt)
			$options[]=array('value'=>$opt->id,'label'=>$opt->{first_string($opt)});
		$name_id = $name."[]";
		$input = tag_select( 
			$name_id, 
			$options, 
			ids($value),null,"multiple" );

		
		break;
	}
	$label = "<label for=\"$name_id\">$name&lt;<i>$type</i>&gt;</label>";
	
	return $label.$input;
}
function admin_get_modelname_from_tablename($table){
	$model_name=$table;
	if(strpos($table,"plugin")!==false){
		$i = strrpos($table,"_")+1;
		$model_name = substring($table,$i,strlen($table));
	}
	return $model_name;
}	
function admin_check_auth(){
	if( !isset( $_SESSION["admin_auth"] ) ){
		redirect( url_for("admin/login") );
	}
}
?>