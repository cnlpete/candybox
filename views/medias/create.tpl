{strip}
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
  <script type="text/javascript">
    $(document).ready(function(){
      $('#input-file').change(function() {
        checkFileSize($(this), {$_SYSTEM.maximumUploadSize.raw}, '{$_SYSTEM.maximumUploadSize.mb|string_format: $lang.error.file.size}');
        prepareForUpload();
      });

      $("input[type='submit']").click(function() {
        upload(this, '/{$_REQUEST.controller}/create.json', '{$_REQUEST.controller}', 'file', 'rename', '/{$_REQUEST.controller}');
        $('#medias').delay('5000').load('/{$_REQUEST.controller}?ajax=1');
      });
    });
  </script>
{/strip}