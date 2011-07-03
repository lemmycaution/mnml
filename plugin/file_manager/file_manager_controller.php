<?
class FileManagerController extends Controller{
	public function index()
	{
		$this->dir = isset($_GET["dir"]) ? $_GET["dir"] : null;
		$this->page = isset($_GET["page"]) ? $_GET["page"] : 0;
		$this->limit = isset($_GET["limit"]) ? $_GET["limit"] : 10;
		$this->files=array();
		$iterator = new DirectoryIterator( UPLOAD_PATH.$this->dir );
		while($iterator->valid()) {
			$file = $iterator->current();
			$filename = $file->getFilename();
			//if($filename!='.' && $filename!='..' && is_file(UPLOAD_PATH.$filename) && substr($filename,0,1)!="." )	
			if(substr($filename,0,1)!="." )
				$this->files[]=$filename;
			$iterator->next();
		}
		$this->count = count($this->files);
	}
	public function search(){
		$this->dir = isset($_GET["dir"]) ? $_GET["dir"] : null;
		$this->page = isset($_GET["page"]) ? $_GET["page"] : 0;
		$this->limit = isset($_GET["limit"]) ? $_GET["limit"] : 10;
		$this->term = isset($_GET["term"]) ? $_GET["term"] : null;		
		$this->files=array();
		$iterator = new DirectoryIterator( UPLOAD_PATH.$this->dir );
		while($iterator->valid()) {
			$file = $iterator->current();
			$filename = $file->getFilename();
			//if($filename!='.' && $filename!='..' && is_file(UPLOAD_PATH.$filename) && substr($filename,0,1)!="." )	
			if(substr($filename,0,1)!="." && strpos($filename,$this->term)!==false)
				$this->files[]=$filename;
			$iterator->next();
		}
		$this->count = count($this->files);
		$this->render('plugin','index');		
		exit;
	}
	public function upload()
	{
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = UPLOAD_PATH;
			$targetName = strtolower( str_replace( " ","-",pathinfo($_FILES['Filedata']['name'],PATHINFO_FILENAME)."_".date("Y-m-d-h-i-s").".".pathinfo($_FILES['Filedata']['name'],PATHINFO_EXTENSION) ) );
			$targetFile =  str_replace('//','/',$targetPath) . $targetName;
			
			// $fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
			// $fileTypes  = str_replace(';','|',$fileTypes);
			// $typesArray = split('\|',$fileTypes);
			// $fileParts  = pathinfo($_FILES['Filedata']['name']);
			
			// if (in_array($fileParts['extension'],$typesArray)) {
				// Uncomment the following line if you want to make the directory if it doesn't exist
				// mkdir(str_replace('//','/',$targetPath), 0755, true);
				
				if(move_uploaded_file($tempFile,$targetFile)){
					flash( array("type"=>"error","text"=>"file(s) successfuly uploaded" ) );
				}else
					flash( array("type"=>"success", "text"=>"file(s) didn't successfuly uploaded" ) );	
			// } else {
			// 	echo 'Invalid file type.';
			// }
		}
	}
	public function delete()
	{
		$this->dir = isset($_GET["dir"]) ? $_GET["dir"] : null;		
		$targetPath = isset($_GET["dir"]) ? UPLOAD_PATH."/".$_GET["dir"]."/" : UPLOAD_PATH ;
		if( unlink($targetPath.$_GET['filename']) )	
			flash(array("text"=>"File: ".$_GET['filename']." deleted","type"=>'success'));
		else
			flash(array("text"=>"An error occured on deleting file: ".$_GET['filename'],"type"=>'error'));	
		redirect( url_for( "file_manager/index" ) );
	}
	/* image editing */
	public function crop()
	{
		$this->dir = isset($_GET["dir"]) ? $_GET["dir"] : null;
		$this->page = isset($_GET["page"]) ? $_GET["page"] : 0;
		$this->limit = isset($_GET["limit"]) ? $_GET["limit"] : null;
		$this->attach = isset($_GET["attach"]) ? $_GET["attach"] : null;
		
		$targetPath = isset($_GET["dir"]) ? UPLOAD_PATH."/".$_GET["dir"]."/" : UPLOAD_PATH ;
		$targetPath =  str_replace('//','/',$targetPath);
		
		$overwrite = isset($_REQUEST["overwrite"]) ? $_REQUEST["overwrite"] : false;
				
		if(VERB=="POST"){
			$crop = ImgResizer::crop($targetPath.$_REQUEST["filename"], $_REQUEST["width"],  $_REQUEST["height"],  $_REQUEST["x1"],  $_REQUEST["y1"],$overwrite);
			if($_REQUEST['target_width'] || $_REQUEST['target_height']){
				$tw = isset($_REQUEST["target_width"]) ? $_REQUEST["target_width"] : null;
				$th = isset($_REQUEST["target_height"]) ? $_REQUEST["target_height"] : null;				
				$resize = ImgResizer::resize($crop['file'],$tw,$th,$overwrite);
				if(!$overwrite)
					unlink($crop['file']);
				flash($resize);							
			}
			flash($crop);			
		}
		$this->filename = $_REQUEST['filename'];
		list($this->width, $this->height, $this->type, $this->attr) = getimagesize(realpath($targetPath.$this->filename));		
	}
}
?>