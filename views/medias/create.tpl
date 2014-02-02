<form class='form-horizontal'>
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
      <input type='file'
             name='file[]'
             id='input-file'
             class='span4 required'
             multiple required />
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
    <input type='button'
           id='js-submit_media'
           class='btn btn-primary'
           value='{$lang.medias.title.create}' />
  </div>
</form>
<script type="text/javascript">
  $(document).ready(function(){
    $('#input-file').change(function() {
      checkFileSize($(this), {$_SYSTEM.maximumUploadSize.raw}, '{$_SYSTEM.maximumUploadSize.mb|string_format: $lang.error.file.size}');
      prepareForUpload();
    });

    $('#js-submit_media').click(function() {
      upload(this, '{$_REQUEST.controller}/create', '{$_REQUEST.controller}', 'file', 'rename', true);
    });
  });
</script>