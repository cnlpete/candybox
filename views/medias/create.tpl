{strip}
  <div class='page-header'>
    <h1>{$lang.medias.title.create}</h1>
  </div>
  <div class='form-horizontal'>
    <div class='control-group'>
      <label for='input-file' class='control-label'>
        {$lang.medias.label.choose} <span title='{$lang.global.required}'>*</span><br />
        <small>
          {if $_SYSTEM.maximumUploadSize.raw <= 1536}
            {$_SYSTEM.maximumUploadSize.b|string_format: $lang.global.upload.maxsize}
          {elseif $_SYSTEM.maximumUploadSize.raw <= 1572864}
            {$_SYSTEM.maximumUploadSize.kb|string_format: $lang.global.upload.maxsize}
          {else}
            {$_SYSTEM.maximumUploadSize.mb|string_format: $lang.global.upload.maxsize}
          {/if}
        </small>
      </label>
      <div class='controls'>
        {* @todo Rename file *}
        <input type='file'
               name='file'
               id='input-file'
               class='span4 required'
               required />
        <span class='help-block'>
          {$lang.medias.info.upload}
        </span>
      </div>
    </div>
    <div class='control-group'>
      <label for='input-rename' class='control-label'>
        {$lang.medias.label.rename}
      </label>
      <div class='controls'>
        <input type='text'
               name='{$_REQUEST.controller}[rename]'
               id='input-rename'
               class='span4'
               onkeyup='this.value = stripNoAlphaChars(this.value)' />
      </div>
    </div>
    <div class='control-group hide' id='js-progress'>
      <label class='control-label'>
        {$lang.global.upload.status}
      </label>
      <div class='controls'>
        <div class='progress progress-success progress-striped active'
             role='progressbar'
             aria-valuemin='0'
             aria-valuemax='100'>
          <div id='js-progress_bar' class='bar'></div>
        </div>
      </div>
    </div>
    <div class='form-actions'>
      <input type='submit'
             class='btn btn-primary'
             value='{$lang.medias.title.create}' />
    </div>
  </div>
  {* TODO: Real redirect *}
  <script type='text/javascript'>
    $('#input-file').change(function() {
      checkFileSize($(this), {$_SYSTEM.maximumUploadSize.raw}, '{$_SYSTEM.maximumUploadSize.mb|string_format: $lang.error.file.size}');
      $('#js-progress').show();
    });

    $("input[type='submit']").click(function() {
      $(this).attr('disabled', '');
      $('.form-actions').append("<input type='reset' class='btn btn-danger' value='{$lang.global.cancel}' />");
      $('.btn-danger').click(function() { document.location.reload()});

      var file = document.querySelector('#input-file').files[0];
      var fd = new FormData();
      fd.append("file", file);
      fd.append("medias[rename]", 'test');

      var xhr = new XMLHttpRequest();
      xhr.open('POST', '/medias/create.json', true);

      xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
          var percentComplete = (e.loaded / e.total) * 100;
          $('#js-progress_bar').css('width', percentComplete + '%');
        }
      };

      xhr.onload = function() {
        window.location.href='/medias';
      };

      xhr.send(fd);
    });
  </script>
{/strip}