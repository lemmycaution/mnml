<?
/*
 * Navigation
 */
function redirect( $target ){
	header("Location: $target");
}
function url_for( $req, $static=false, $lang=false ){
	if($static) 
		return str_replace("boot.php/","",BASE_PATH).$req;
	if($lang)
		return BASE_PATH.LANG."/".$req;
	return BASE_PATH.$req;
}
/*
 * Html
 */
function parserichtext($s){
	$s = str_replace( "&ouml;", "ö" , $s);
	$s = str_replace( "&ccedil;", "ç", $s);
	$s = str_replace( "&uuml;", "ü", $s);
	$s = str_replace( "&Ouml;", "Ö", $s);
	$s = str_replace( "&Ccedil;", "Ç", $s);
	$s = str_replace( "&Uuml;", "Ü", $s);
	$s = htmlentities($s,ENT_COMPAT,'UTF-8');	
	return $s;
}
function tag_a( $title, $href, $class=null, $options=null){
	$class = $class ? "class=\"$class\"": $class;
	$tag = "<a href=\"$href\" title=\"$title\" $class $options >$title</a>";
	return $tag;
}
function tag_label_and_input( $name, $type, $value, $class=null){
	$label = "<label for=\"$name\">$name&lt;<i>$type</i>&gt;</label>";
	$input = tag_input( $name, $type, $value, $class );
	$tag = $label.$input;
	return $tag;
}
function tag_input( $name, $type, $value, $class=null ){
	$tag = "<input type=\"$type\" name=\"$name\" value=\"$value\" class=\"field $class\" />";
	return $tag;
}
function tag_select( $name, $options=array(), $default=null, $class=null, $multiple=null ){
	$tag = "<select name=\"$name\" class=\"field $class\" $multiple>";
	foreach($options as $option){
		if($option['value']==$default || ($multiple &&  in_array($option['value'],$default) ) )
			$tag.= "<option value=\"".$option['value']."\" selected=\"true\" >".$option['label']."</option>";		
		else
			$tag.= "<option value=\"".$option['value']."\">".$option['label']."</option>";
	}
	$tag.= "</select>";
	return $tag;
}
function tag_textarea( $name, $value, $class=null ){
	$tag = "<textarea name=\"$name\" class=\"field $class\">$value</textarea>";
	return $tag;
}
/*
 * Output formatters
 */
function send_json($data, $compress = true)
{
    if ($compress) {
        ob_start('ob_gzhandler');
    }

    echo json_encode($data);

    exit;
}
function send_xml($data, $name=null)
{
	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
	echo array_to_xml($data);
    exit;
}
/**
 * Array
 */
function unidimensionalize($haystack)
{
    $values = array();

    foreach ($haystack as $value) {
        if (is_array($value)) {
            $more_values = unidimensionalize($value);

            $values = array_merge($values, $more_values);
        } else {
            $values[] = $value;
        }
    }

    return $values;
}
/*
 * Converters
 */
# object_to_array()
# =================
#
# Converts the given object to an associative array.
# Starting on the second argument you can specify the fields on the object you want to get back. The function can take an array or a list of strings containing the fields. If no fields are given, all valid fields for the object will be returned.
# This function is ideal when working with ajax requests and you want to transfer some object with json because you can save bandwitch printing only the fields you need.
# These fields can't be object relations. Only direct property objects can be converted.
#
#
#
# Arguments
# ---------
#
# * object: the object to convert to an array.
# * fields: the fields to get back. If no fields are given all valid fields on the object will be returned.
#
#
#
# Returns
# -------
#
# * an array with the given or all object's fields.
#
#
#
# Examples
# --------
#
# The simplest way to run this function is only passing the object you want to convert.
#
#	$user = new User;
#	object_to_array($user);
#
#	Array
#	(
#		'id' => 5,
#		'name' => 'John',
#		'surname' => 'Smith',
#		'age' => 24
#	)
#
# As said before, you can specify the fields to return on the array.
#
#	$user = new User;
#	object_to_array($user, array('name', 'surname'));
#
#	Array
#	(
#		'name' => 'John',
#		'surname' => 'Smith'
#	)
#
#	The fields to get back can be given as a list of strings.
#
#	$user = new User;
#	object_to_array($user, 'surname', 'age');
#
#	Array
#	(
#		'surname' => 'Smith',
#		'age' => 24
#	)
function object_to_array() {
	$fields = func_get_args();
	$object = array_shift($fields);

	if(is_object($object) && get_parent_class( get_parent_class($object) )=="ActiveRecord"){
	if (count($fields) == 0)
		$fields = $object->columns();
	elseif (is_array($fields[0]))
		$fields = $fields[0];

	$data = array();
	foreach ($fields as $field)
		$data[$field] = $object->$field;
}else{
	$data = $object;
	
}
	return $data;
}

# objects_to_array()
# ==================
#
# Converts the given array of objects to an array of associative arrays.
# It works like object_to_array() but receives an array of objects and returns an array of arrays.
#
#
#
# Arguments
# ---------
#
# * objects: an array of objects to convert to an array.
# * fields: the fields to get back. If no fields are given all valid fields on the object will be returned.
#
#
#
# Returns
# -------
#
# * an array filled with arrays with the given or all object's fields.

function objects_to_array() {
	
	$fields = func_get_args();
	$objects = array_shift($fields);

	$data = array();
	foreach ($objects as $object){
		$data[] = !empty($fields) ? object_to_array($object, $fields) : object_to_array($object) ;
	}	

	return $data;
}
function array_to_xml($data,$name=null,$tab=0){
	$name = $name ? $name : "data";
	$_tab="";
	for($i=0;$i<$tab;$i++)$_tab.="\t";
	$_dtab="$_tab\t";
	$xml="$_tab<$name>\n";
	foreach($data as $key=>$value){	
		$key = is_numeric($key) ? gettype($value) : str_replace(" ","-",$key);
		if( is_array($value) )
    		$xml.=array_to_xml($value,$key,$tab+1);
		else
			$xml.="$_dtab<$key><![CDATA[$value]]></$key>\n";
	}
	$xml.="$_tab</$name>\n";
	return $xml;
}
/*
 * Mail
 */
function send_mail($to,$from,$subject,$body,$attachment=null){
	$mail = new PhpMailer();
	$mail->IsHTML(true);
	if( defined("USE_SMTP") ){
		global $_smtp;
		$mail->IsSMTP();
		$mail->Host = $_smtp['host'];
		$mail->SMTPAuth = $_smtp['auth'];
		$mail->Username = $_smtp['username'];
		$mail->Password = $_smtp['password'];
	}
	
	$mail->AddAddress( $to );
	$mail->From = $mail->FromName = $mail->Sender = $from;
	$mail->Subject = $subject;
	$mail->Body = $body;
	if($attachment)
		$mail->AddAttachment($attachment);
		
	if(!$mail->Send())
	{
	   return $mail->ErrorInfo;
	}
	else
	{
	   return true;
	}
}
/*
 * Session
 */
function clear_session( $name ){
	$_SESSION[$name] = null;
	unset( $_SESSION[$name] );
}
/*
 * Time & Date
 */
function now(){
    //$date_now = mktime(0, 0, 0);
	//return time() - $date_now;
	return date('Y-m-d H:i:s', time());
}
/*
 * String
 */
function substring($string, $from, $to) {
	return substr($string, $from, ++$to - $from);
}
/*
 * Framework
 */
function http_error ( $code ){
	switch ( $code ){
		case 404:
		// Page was not found:
		//header('HTTP/1.1 404 Not Found');		
		header("Status: 404 Not Found");		
		break;
		case 403:
		// Access forbidden:
		header('HTTP/1.1 403 Forbidden');
		break;
		case 500:
		// Server error
		header('HTTP/1.1 500 Internal Server Error');
		break;
	}
	include (PUBLIC_PATH."error.php");
	exit;
}
function error( $e ){
	if ( is_array($e) && isset($e['error']) ){
		$code = $e["error"];
	}else{
		$code = "Unknown";
	}
	include (PUBLIC_PATH."error.php");
	exit;
}
function flash( $msg = null ){
	if(!$msg){
		if( array_key_exists( "flash_msg", $_SESSION ) ){
			$msg = $_SESSION["flash_msg"];
			clear_session("flash_msg");
			echo "<p class=\"".$msg["type"]."\">" . $msg["text"] . "</p>";		
		}
	}else{	
		$_SESSION["flash_msg"] = ( $msg );
	}
}
function first_string( $model, $row=null ){
	foreach($model->attributes_info() as $attr){
		if($attr['type']=="string"){
			if($row) return $row[$attr['name']];
			else return $attr['name'];
		}
	}
	return $model->_primary_key;
}
function dir_files( $path, $recursive=false ){
	$files = array();
	$iterator = new DirectoryIterator( $path );
	while($iterator->valid()) {
		$i = $iterator->current();
		if($i!="." && $i!=".."){
			$dir = $path.DIRECTORY_SEPARATOR.$i;

			if(is_dir($dir) && $recursive ){
				$files[] = dir_files( $dir, $recursive );
			}else
				$files[] = $dir;
		}
		$iterator->next();
	}	
	return $files;
}
# get_ip()
# ========
#
# Gets the request ip address.
#
#
#
# Returns
# -------
#
# * a string containing the ip address or null if it can't be reached.
function get_ip() {
	return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['HTTP_VIA']) ? $_SERVER['HTTP_VIA'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null));
}
/**
 * 
 * @param string $type [optional] Fill this variable with the mobile request
 *                                type (iphone, android, opera, blackberry,
 *                                palm or windows).
 * @return boolean true if the request is a mobile request, false otherwise.
 */
function is_mobile(string &$type = null)
{
    // get the user agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $user_agent = strtolower($user_agent);

    // check iphone os
    if (strpos($user_agent, 'iphone') !== false || strpos($user_agent, 'ipod') !== false) {
        $type = 'iphone';

        return true;
    }

	// check ipad
    if (strpos($user_agent, 'ipad') !== false ) {
        $type = 'ipad';

        return true;
    }

    // check android
    if (strpos($user_agent, 'android') !== false) {
        $type = 'android';

        return true;
    }

    // check opera mobile
    if (strpos($user_agent, 'opera mini') !== false) {
        $type = 'opera';

        return true;
    }

    // check blackberry
    if (strpos($user_agent, 'blackberry') !== false) {
        $type = 'blackberry';

        return true;
    }

    // check palm
    if (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|fennec|plucker|xiino|blazer|elaine)/', $user_agent)) {
        $type = 'palm';

        return true;
    }

    // check windows
    if (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/', $user_agent)) {
        $type = 'windows';

        return true;
    }

    // check some other mobile agents
    if (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/', $user_agent)) {
        $type = 'unknown';

        return true;
    }

    // get the accept header to check some other options
    $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;

    if (strpos($accept, 'text/vnd.wap.wml') !== false || strpos($accept, 'application/vnd.wap.xhtml+xml') !== false) {
        $type = 'unknown';

        return true;
    }

    if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
        $type = 'unknown';

        return true;
    }

    // check some other user agents
    $other_user_agents = array(
        '1207', '3gso', '4thp', '501i', '502i', '503i', '504i', '505i', '506i', '6310', '6590', '770s', '802s', 'a wa',
        'acer', 'acs-', 'airn', 'alav', 'asus', 'attw', 'au-m', 'aur ', 'aus ', 'abac', 'acoo', 'aiko', 'alco', 'alca',
        'amoi', 'anex', 'anny', 'anyw', 'aptu', 'arch', 'argo', 'bell', 'bird', 'bw-n', 'bw-u', 'beck', 'benq', 'bilb',
        'blac', 'c55/', 'cdm-', 'chtm', 'capi', 'cond', 'craw', 'dall', 'dbte', 'dc-s', 'dica', 'ds-d', 'ds12', 'dait',
        'devi', 'dmob', 'doco', 'dopo', 'el49', 'erk0', 'esl8', 'ez40', 'ez60', 'ez70', 'ezos', 'ezze', 'elai', 'emul', 
        'eric', 'ezwa', 'fake', 'fly-', 'fly_', 'g-mo', 'g1 u', 'g560', 'gf-5', 'grun', 'gene', 'go.w', 'good', 'grad',
        'hcit', 'hd-m', 'hd-p', 'hd-t', 'hei-', 'hp i', 'hpip', 'hs-c', 'htc ', 'htc-', 'htca', 'htcg', 'htcp', 'htcs',
        'htct', 'htc_', 'haie', 'hita', 'huaw', 'hutc', 'i-20', 'i-go', 'i-ma', 'i230', 'iac ', 'iac-', 'iac/', 'ig01',
        'im1k', 'inno', 'iris', 'jata', 'java', 'kddi', 'kgt ', 'kgt/', 'kpt ', 'kwc-', 'klon', 'lexi', 'lg g', 'lg-a',
        'lg-b', 'lg-c', 'lg-d', 'lg-f', 'lg-g', 'lg-k', 'lg-l', 'lg-m', 'lg-o', 'lg-p', 'lg-s', 'lg-t', 'lg-u', 'lg-w',
        'lg/k', 'lg/l', 'lg/u', 'lg50', 'lg54', 'lge-', 'lge/', 'lynx', 'leno', 'm1-w', 'm3ga', 'm50/', 'maui', 'mc01',
        'mc21', 'mcca', 'medi', 'meri', 'mio8', 'mioa', 'mo01', 'mo02', 'mode', 'modo', 'mot ', 'mot-', 'mt50', 'mtp1',
        'mtv ', 'mate', 'maxo', 'merc', 'mits', 'mobi', 'motv', 'mozz', 'n100', 'n101', 'n102', 'n202', 'n203', 'n300',
        'n302', 'n500', 'n502', 'n505', 'n700', 'n701', 'n710', 'nec-', 'nem-', 'newg', 'neon', 'netf', 'noki', 'nzph',
        'o2 x', 'o2-x', 'opwv', 'owg1', 'opti', 'oran', 'p800', 'pand', 'pg-1', 'pg-2', 'pg-3', 'pg-6', 'pg-8', 'pg-c',
        'pg13', 'phil', 'pn-2', 'pt-g', 'palm', 'pana', 'pire', 'pock', 'pose', 'psio', 'qa-a', 'qc-2', 'qc-3', 'qc-5',
        'qc-7', 'qc07', 'qc12', 'qc21', 'qc32', 'qc60', 'qci-', 'qwap', 'qtek', 'r380', 'r600', 'raks', 'rim9', 'rove',
        's55/', 'sage', 'sams', 'sc01', 'sch-', 'scp-', 'sdk/', 'se47', 'sec-', 'sec0', 'sec1', 'semc', 'sgh-', 'shar',
        'sie-', 'sk-0', 'sl45', 'slid', 'smb3', 'smt5', 'sp01', 'sph-', 'spv ', 'spv-', 'sy01', 'samm', 'sany', 'sava',
        'scoo', 'send', 'siem', 'smar', 'smit', 'soft', 'sony', 't-mo', 't218', 't250', 't600', 't610', 't618', 'tcl-',
        'tdg-', 'telm', 'tim-', 'ts70', 'tsm-', 'tsm3', 'tsm5', 'tx-9', 'tagt', 'talk', 'teli', 'topl', 'hiba', 'up.b',
        'upg1', 'utst', 'v400', 'v750', 'veri', 'vk-v', 'vk40', 'vk50', 'vk52', 'vk53', 'vm40', 'vx98', 'virg', 'vite',
        'voda', 'vulc', 'w3c ', 'w3c-', 'wapj', 'wapp', 'wapu', 'wapm', 'wig ', 'wapi', 'wapr', 'wapv', 'wapy', 'wapa',
        'waps', 'wapt', 'winc', 'winw', 'wonu', 'x700', 'xda2', 'xdag', 'yas-', 'your', 'zte-', 'zeto', 'acs-', 'alav',
        'alca', 'amoi', 'aste', 'audi', 'avan', 'benq', 'bird', 'blac', 'blaz', 'brew', 'brvw', 'bumb', 'ccwa', 'cell',
        'cldc', 'cmd-', 'dang', 'doco', 'eml2', 'eric', 'fetc', 'hipt', 'http', 'ibro', 'idea', 'ikom', 'inno', 'ipaq',
        'jbro', 'jemu', 'java', 'jigs', 'kddi', 'keji', 'kyoc', 'kyok', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-', 'libw',
        'm-cr', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'mywa', 'nec-', 'newt', 'nok6',
        'noki', 'o2im', 'opwv', 'palm', 'pana', 'pant', 'pdxg', 'phil', 'play', 'pluc', 'port', 'prox', 'qtek', 'qwap',
        'rozo', 'sage', 'sama', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal',
        'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh', 'treo', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda',
        'vx52', 'vx53', 'vx60', 'vx61', 'vx70', 'vx80', 'vx81', 'vx83', 'vx85', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr',
        'webc', 'whit', 'winw', 'wmlb', 'xda-'
    );   

    $user_agent = substr($user_agent, 0, 4);

    if (in_array($user_agent, $other_user_agents)) {
       $type = 'unknown';

       return true;
    } 

    return false;
}
/* internally used functions */
function ids($objects) {
	$data = array();
	if($objects){
	foreach ($objects as $object)
		$data[] = $object->{$object->_primary_key};
	}
	return $data;
}
function has_model( $file ){
	$native = file_exists( MODELS_PATH . DIRECTORY_SEPARATOR . $file. ".php" );
	$plugin = file_exists( PLUGIN_PATH . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $file. ".php" );	
	return $native ? $file : ( $plugin ? $plugin : false );
}
/* using by controller class */
function check_controller_file( $file ){
	$native = file_exists( CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $file . "_controller.php" );
	$plugin = file_exists( PLUGIN_PATH . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $file . "_controller.php" );
	return $native ? $file : ( $plugin ? "plugin" : false );
}
/* using by application class */
function check_named_parts($v1,$v2){
	if( strpos($v1,':')===false && $v1!=$v2) return 1;
	elseif( strpos($v1,':')!==false ) return 0;
	return -1;
}
/* plugin */
function has_plugin ( $name ){
	return file_exists( PLUGIN_PATH.$name ) | file_exists( PLUGIN_PATH.$name.".php" );
}
function require_plugin ( $name ){
	require_once PLUGIN_PATH.$name.DIRECTORY_SEPARATOR."require.php";
}
?>