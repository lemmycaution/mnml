<? /* this file must be required in admin/layout.html.php file */ ?>
<script type="text/javascript">
var file_manager_attach_source;
var file_manager_onFilemanagerPopupFunctions = [];
function file_manager_onFilemanagerPopup(){
	for (var i=0;i<file_manager_onFilemanagerPopupFunctions.length;i++)
		file_manager_onFilemanagerPopupFunctions[i]();
}
function file_manager_set_attach_source(e){
	file_manager_attach_source = $(e);
}
admin_onPopupFunctions.push(function(){
	$("input[name~=file]").after('<?=
		tag_a(
			" file manager" , 
			url_for("file_manager/index?attach=true") , 
			"popup" , 
			"onclick=\"file_manager_set_attach_source($(this).prev())\"" 
		);
	?>');
});
admin_onReadyFunctions.push(function(){		
	$(".filemanager").fancybox({'titleShow':false,'type':'iframe','width':960,'height':500,'onComplete':file_manager_onFilemanagerPopup});
});

</script>