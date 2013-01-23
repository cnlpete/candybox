{* Add and run jQuery.timeago() *}
<script type='text/javascript' src='/vendor/tiny_mce/jquery.tinymce.js'></script>
<script type='text/javascript'>
  $(document).ready(function(){
    $('textarea.js-tinymce').tinymce({
      script_url : '/vendor/tiny_mce/tiny_mce.js',
      theme : 'advanced',
      plugins : 'autosave,safari,style,advimage,advlink,inlinepopups,insertdatetime,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
      theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,cut,copy,paste,pastetext,|,search,replace,|,fullscreen',
      theme_advanced_buttons2 : 'styleselect,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor',
      theme_advanced_buttons3 : 'hr,|,link,unlink,anchor,|,image,|,cleanup,removeformat,|,code,|,insertdate,inserttime,|,outdent,indent,|,sub,sup,|,charmap',
      theme_advanced_statusbar_location : 'bottom',
      theme_advanced_resizing : true,
      language : '{$WEBSITE_LANGUAGE}',
      remove_script_host : false,
      convert_urls : false,
      entity_encoding : 'raw',
      height : '300px'
    });
  });
</script>