<? /* this file must be required in admin/layout.html.php file */ ?>
<style>
textarea{width:670px !important;}
.tiny_mce_bar{margin:0;}
.tiny_mce_bar a{padding:5px;border:1px solid #ccc;cursor:hand;cursor:pointer;float:left;display:block;margin:0 0 -5px 0}
.tiny_mce_bar a.right {margin:0 0 -5px -1px;float:right;display:block;}
</style>
<script type="text/javascript" src="<?=url_for("js/lib/tiny_mce/jquery.tinymce.js")?>"></script>
<script type="text/javascript">
function tiny_mce_set_tiny_mode(m){
	if(m==0){
		$('textarea.richtext').each(function(){$(this).tinymce().show();});
	}else{
		$('textarea.richtext').each(function(){$(this).tinymce().hide();});
	}
}
function tiny_mce_init_tiny_mce(){
		$('textarea.richtext').tinymce({
			script_url : '<?=url_for("js/lib/tiny_mce/tiny_mce_gzip.php")?>',
			theme : "advanced",
			plugins : "pagebreak,iespell,media,paste,directionality,fullscreen,xhtmlxtras",
			theme_advanced_buttons1 :"bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,pagebreak,|,iespell,fullscreen",
			theme_advanced_buttons2 :"formatselect,underline,justifyfull,forecolor,|,paste,pastetext,pasteword,removeformat,|,media,charmap,|,outdent,indent,|,undo,redo",
			theme_advanced_buttons3:"",theme_advanced_buttons4:"",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
			theme_advanced_resize_horizontal : false,
			convert_urls : false,
			width:"685",
			// Example content CSS (should be your site CSS)
			content_css : "<?=url_for("css/desktop.css")?>",
			//
			setup : function(ed) {
			        ed.onInit.add(function(ed) {
						$('body').scrollTop(0);
						$.fancybox.resize();$.fancybox.center();
					})
			}
		});
		$('textarea.richtext').before('<div class="tiny_mce_bar">'+
		'<a class="right" onclick="tiny_mce_set_tiny_mode(1)">Code</a>'+
		'<a class="right" onclick="tiny_mce_set_tiny_mode(0)">Visual</a>'+
		'</div>');
		//file manager button
		<? if( has_plugin("file_manager") ) {?>
		$('.tiny_mce_bar').prepend('<?=tag_a(
			"file manager" , 
			url_for("file_manager/index?attach=true") , 
			"popup"
		);
		?>');
		$.easy.popup();
		<? }?>
}
admin_onPopupFunctions.push(tiny_mce_init_tiny_mce);
function tiny_mce_update_insert_function(){
	setTimeout(function(){
		$("iframe").contents().find("a.insert").click(function(){
			var path = '<?=url_for("upload/")?>'+$(this).attr("rel");
			if(path.indexOf("jpg")>-1 || path.indexOf("png")>-1 || path.indexOf("gif")>-1){
				content = '<img src="'+path+'" />';
			}else{
				content = '<a href="'+path+'">'+$(this).attr("rel")+'</a>';
			}
			$('textarea.richtext').each(function(){$(this).tinymce().execCommand('mceInsertContent',false,content);});
			tiny_mce_set_tiny_mode(1);
			tiny_mce_set_tiny_mode(0);
			$('#easy_popup').remove();
			$('#easy_popupcontent').remove();		
		});
	},2000);	
};
</script>