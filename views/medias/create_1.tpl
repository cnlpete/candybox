{strip}
  <div class='page-header'>
    <h1>{$lang.medias.title.create}</h1>
  </div>
  <form method='post'
        class='form-horizontal'
        enctype='multipart/form-data'
        action='/{$_REQUEST.controller}/{if isset($_REQUEST.id)}{$_REQUEST.id}/{/if}{$_REQUEST.action}'>
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
    <div class='form-actions'>
      <input type='submit'
             class='btn btn-primary'
             value='{$lang.medias.title.create}' />
    </div>
  </form>
  <script type='text/javascript'>
    $("input[type='submit']").click(function() {
      $(this).hide();
      $('.form-actions').append("<img src='{$_PATH.images}/candy.global/loading.gif' alt='" + lang.loading + "' />");
    });

    $('#input-file').change(function() {
      checkFileSize($(this),
        {$_SYSTEM.maximumUploadSize.raw},
        '{$_SYSTEM.maximumUploadSize.mb|string_format: $lang.error.file.size}');
    });
  </script>
{/strip}