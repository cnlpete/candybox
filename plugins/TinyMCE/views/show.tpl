<script type='text/javascript' src='/vendor/tinymce/tinymce/js/tinymce/tinymce.min.js'></script>
<script type='text/javascript'>
  $(document).ready(function(){
    tinymce.init({
      selector: 'textarea.js-editor',
      script_url : '/vendor/tinymce/tinymce.min.js',
      theme : 'modern',
      plugins: [
          "advlist autolink lists link image charmap print preview anchor",
          "searchreplace visualblocks code fullscreen",
          "insertdatetime media table contextmenu paste moxiemanager"
      ],
      toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
      language : '{$WEBSITE_LANGUAGE}',
      remove_script_host : false,
      convert_urls : false,
      entity_encoding : 'raw',
      height : '300px'
    });
  });
</script>