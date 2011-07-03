<?
	$attach = (isset($_GET['attach'])) ? "&attach=true" : "";
?>
<h1>Listing of Files</h1>

<table cellpadding="0" cellspacing="0">
	<caption>
		Files
	</caption>
	<thead>
		<tr>
			<th>file name</th>
			<th></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th>file name</th>
			<th></th>
		</tr>
	</tfoot>
	<tbody>
		<?
		$page = isset($page)?$page:0;
		$start=$page*$limit;
		$end=$start+$limit;
		for ($i=$start;$i<$end;$i++){
			if(isset($files[$i])){
			$file = $files[$i];
			$is_image=in_array( strtolower( pathinfo($file,PATHINFO_EXTENSION) ),array("jpg","png","gif") );
			$is_dir=is_dir(UPLOAD_PATH."$dir/$file");			

			
		?>
		<? if($i==0 && realpath(UPLOAD_PATH."$dir/..").DIRECTORY_SEPARATOR!=PUBLIC_PATH):?>
		<tr><td>
			..
			<div class="file_row">
				<?
				echo tag_a( 'go upside', 
							url_for("file_manager/index?dir=$dir/..&page=$page&limit=$limit".$attach)
							);
				?>
			</div>	
		</td></tr>	
		<? endif;?>
		<tr>
			<td>
				<?=$file;?>
				<div class="file_row">
					<?
					if(!$is_dir){
						if( isset($_GET['attach']) ){
							$rel = $dir ? substr("$dir/$file",1) : $file;
							echo tag_a( 'insert', 
										"javascript:void(0);",
										"insert",
										"rel=\"$rel\"")." | ";
						}			
						if( $is_image ){			
							echo tag_a( 'view', 
										url_for( "upload/$dir/$file" ),
										"external")." | ";
							echo tag_a( 'crop', 
										url_for("file_manager/crop?dir=$dir&page=$page&limit=$limit&filename=$file".$attach)
										)." | ";
						}
						echo tag_a( 'delete', 
									url_for("file_manager/delete?dir=$dir&filename=$file".$attach)
									);
					}else{
						echo tag_a( 'go inside', 
									url_for("file_manager/index?dir=$dir/$file&page=$page&limit=$limit".$attach)
									);
					}			
					?>
				</div>
			</td>
			<td class="thumb">
				<?if( $is_image ) {?>
				<img width="100px" src="<?=url_for("upload/$dir/$file")?>" />
				<?}?>
			</td>
		</tr>
		<?}}?>
	</tbody>	
</table>
<ol class="pagination fixed">
	<?
	$page_prev=($page>0)?$page-1:0;
	$page_next=($page<floor($count/$limit))?$page+1:floor($count/$limit);
	?>
	<li><?=tag_a("&lt;",url_for("file_manager/index?page=$page_prev$attach"));?></li>
	<?for($i=0;$i<ceil($count/$limit);$i++){?>
	<li><?=tag_a($i,url_for("file_manager/index?page=$i$attach"));?></li>
	<?}?>	
	<li><?=tag_a("&gt;",url_for("file_manager/index?page=$page_next$attach"));?></li>
</ol>