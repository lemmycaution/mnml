<script type="text/javascript">
$(document).ready(function() {
	$("#uploadify").uploadify({
		'uploader'       : '<?=url_for("js/plugin/jquery.uploadify/uploadify.swf")?>',
		'script'         : '<?=url_for("file_manager/upload")?>',
		'cancelImg'      : '<?=url_for("js/plugin/jquery.uploadify/images/cancel.png")?>',
		'folder'         : '<?=url_for("upload")?>',
		'queueID'        : 'fileQueue',
		'auto'           : true,
		'multi'          : true
	});
});
</script>
<div>
	<input type="file" name="uploadify" id="uploadify" /> <button onclick="$('#uploadify').uploadifyClearQueue()" style="vertical-align:top;height:30px">Cancel All Uploads</button>
</div>
<div id="fileQueue"></div>

<p class="toggle expanded">if you have some trouble with flash uploader try old html one by clicking here</p>
<form action="<?=url_for("file_manager/upload")?>" enctype="multipart/form-data">
<input name="Filedata" type="file" />
<input type="submit" value="upload" />
</form>
