<?
class ImgResizer
{
	public static function resize( $file, $width=null, $height=null, $overwrite=false, $named=null)
	{
		list($iwidth, $iheight, $itype, $iattr) = getimagesize($file);
		if($width && !$height){		
			$height = floor( $iheight * ($width/$iwidth) );
		}elseif($height && !$width){
			$width = floor( $iwidth * ($height/$iheight) );				
		}elseif(!$width && !$height){
			return ( array( "text"=>"Thumbnail failed to create, missing argument", "type"=>"error" ) );
		}
				
		$check = false;
		
		$file_dir = strtolower( pathinfo($file,PATHINFO_DIRNAME) );
		$file_name = strtolower( pathinfo($file,PATHINFO_FILENAME) );
		$ext = strtolower( pathinfo($file,PATHINFO_EXTENSION) );
		
		$filename = $overwrite ? $file_name . "." . $ext : 
					  ( $named ? $file_name . "_" . $named . "." . $ext : 
						$file_name . "_" . $width . "x" . $height . "." . $ext) ;
		$newFile =  $file_dir . DIRECTORY_SEPARATOR . $filename;
		
		//quality
		$quality = ($ext=="jpg")?100:9;
					
		//reader
		$function_to_read = "ImageCreateFrom".(($ext=="jpg")?"Jpeg":ucfirst($ext));
		
		//writer
		$function_to_write = "Image".(($ext=="jpg")?"Jpeg":ucfirst($ext));
		
		// Read the source file
	    $source_handle = $function_to_read($file);
	   
	    // Create a blank image
	    $destination_handle = ImageCreateTrueColor($width, $height);
	 
	    // Put the cropped area onto the blank image
	    $check = ImageCopyResampled($destination_handle, $source_handle, 0, 0, 0, 0, $width, $height, $iwidth, $iheight);
	 
	    // Save the image
	    $function_to_write($destination_handle, $newFile, $quality);
	    ImageDestroy($destination_handle);
	 
	    // Check for any errors
	    if ($check)
	    {
	       return ( array ( "text"=>"Thumbnail created", "type"=>"success", "file"=>$newFile, "size"=>getimagesize($newFile) ) );
	    } else
	    {
	       return ( array( "text"=>"Thumbnail failed to create", "type"=>"error" ) );
	    }
	}
	
	public static function crop( $file, $width, $height, $x, $y, $overwrite=false, $named=null )
	{
			$check = false;

			$file_dir = strtolower( pathinfo($file,PATHINFO_DIRNAME) );			
			$file_name = strtolower( pathinfo($file,PATHINFO_FILENAME) );
			$ext = strtolower( pathinfo($file,PATHINFO_EXTENSION) );
			
			$filename = $overwrite ? $file_name . "." . $ext : 
						  ( $named ? $file_name . "_" . $named . "." . $ext : 
							$file_name . "_" . $width . "x" . $height . "." . $ext) ;
			$newFile = $file_dir . DIRECTORY_SEPARATOR . $filename;
			//quality
			$quality = ($ext=="jpg")?100:9;
						
			//reader
			$function_to_read = "ImageCreateFrom".(($ext=="jpg")?"Jpeg":ucfirst($ext));
			//writer
			$function_to_write = "Image".(($ext=="jpg")?"Jpeg":ucfirst($ext));
			
			// Read the source file
		    $source_handle = $function_to_read($file);
		 
		   
		    // Create a blank image
		    $destination_handle = ImageCreateTrueColor($width, $height);
		 
		    // Put the cropped area onto the blank image
		    $check = ImageCopyResampled($destination_handle, $source_handle, 0, 0, $x, $y, $width, $height, $width, $height);
		    
		 
		    // Save the image
		    $function_to_write($destination_handle, $newFile, $quality);
		    ImageDestroy($destination_handle);
		 
		    // Check for any errors
		    if ($check)
		    {
		       return ( array ( "text"=>"Thumbnail created", "type"=>"success", "file"=>$newFile, "size"=>getimagesize($newFile) ) );
		    } else
		    {
		        return ( array( "text"=>"Thumbnail failed to create", "type"=>"error" ) );
		    }
		}
}
?>