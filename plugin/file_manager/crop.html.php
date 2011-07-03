<div class="main">
<?=tag_a( 'back', url_for("file_manager/index?dir=$dir&page=$page&limit=$limit".$attach) )?>	
<h2>Cropping Image <?=$filename?></h2>
	
	<div id="imgcontainer">
		<img id="img_to_crop" src="<?=url_for("upload/$dir/$filename")?>" width="100%" />
		<table>
			<thead>
				<tr>
					<th>Key</th> 
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>arrow keys</td><td>move selection area by 10 pixels</td>
				</tr>	
				<tr>
					<td>Shift + arrow keys</td><td>move selection area by 1 pixel</td>
				</tr>
				<tr>
					<td>Ctrl + arrow keys</td><td>resize selection area by 10 pixels</td>
				</tr>
				<tr>
					<td>Shift + Ctrl + arrow keys</td><td>resize selection area by 1 pixel</td>
				</tr>
			</tbody>
		</table>	
	</div>	
</div>				
<div class="secondary">
<form action="<?=url_for("file_manager/crop?dir=$dir&filename=$filename")?>" method="POST" >
		
		<input type="hidden" name="filename" value="<?=$filename?>" />

		<div>
			<label>overwrite</label>
			<input type="checkbox" name="overwrite" class="small" checked/>
		</div>
				
		<div>
			<label>Max Width</label>
			<input type="text" name="maxWidth" class="small" />
		</div>
		
		<div>
			<label>Max Height</label>
			<input type="text" name="maxHeight" class="small"  />
		</div>
		
		<div>
			<label>Aspect Ratio</label>
			<input type="text" name="aspectRatio" class="small" value="1:1" />
		</div>
			
		<div>
			<label>x1</label>
			<input type="text" name="x1" class="small" />
		</div>
		
		<div>
			<label>y1</label>
			<input type="text" name="y1" class="small" />
		</div>
		
		<div>
			<label>x2</label>
			<input type="text" name="x2" class="small" />
		</div>
		
		<div>
			<label>y2</label>
			<input type="text" name="y2" class="small" />
		</div>
		
		<div>
			<label>width</label>
			<input type="text" name="width" class="small" />
		</div>
		
		<div>
			<label>height</label>
			<input type="text" name="height" class="small" />
		</div>
		
		<div>
			<label>target width</label>
			<input type="text" name="target_width" class="small" />
		</div>
		
		<div>
			<label>target height</label>
			<input type="text" name="target_height" class="small" />
		</div>
		
		<button type="submit">Create</button>	
</form>
</div>		
<script>
$(document).ready(function () {  
	//$('<div><img id="img_preview" src="<?=url_for("upload/$dir/$filename")?>" style="position: relative;" /><div>') .css({ float: 'right', position: 'relative', overflow: 'hidden', width: '100px', height: '100px', border:'1px dotted #ff0000' }) .insertBefore($('form')); 
	var ias = $('#img_to_crop').imgAreaSelect({ 
		instance:true,
		imageWidth:<?=$width?>,
		imageHeight:<?=$height?>,
		aspectRatio:"1:1",
		keys:true,
		onSelectEnd: function (img, selection) { 
			$('input[name=x1]').val(selection.x1); 
			$('input[name=y1]').val(selection.y1); 
			$('input[name=x2]').val(selection.x2); 
			$('input[name=y2]').val(selection.y2); 
			$('input[name=width]').val(selection.width); 
			$('input[name=height]').val(selection.height); 
		},
		onSelectChange: function (img, selection) { 
			/*
			var ar = $("input[name=aspectRatio]").val().split(":");
			var scaleX = 100*ar[0] / (selection.width || 1); 
			var scaleY = 100*ar[1] / (selection.height || 1); 
			
			$('#img_preview').css({ 
				width: Math.round(scaleX * <?=$width?>) + 'px', 
				height: Math.round(scaleY * <?=$height?>) + 'px', 
				marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
				marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
			}); 
			
			$('#img_preview').parent().width(100*ar[0]).height(100*ar[1]);
			*/
		} 
	}); 
	$('input[name=maxWidth]').change(function(){
		var p = $('#img_to_crop').width()/<?=$width?>;
		ias.setOptions({maxWidth:$(this).val()*p});
		ias.update();
	});
	$('input[name=maxHeight]').change(function(){
		var p = $('#img_to_crop').height()/<?=$height?>;
		ias.setOptions({maxHeight:$(this).val()*p});
		ias.update();
	});
	$('input[name=aspectRatio]').change(function(){
		ias.setOptions({aspectRatio:$(this).val()});
		ias.update();
	});
});
</script>