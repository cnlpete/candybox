{strip}
  <form action='/{$_REQUEST.controller}/{$_REQUEST.id}/{$_REQUEST.action}'
        method='post'
        enctype='multipart/form-data'
        class='form-horizontal'>
    <div class='page-header'>
      <h1>
        {if $_REQUEST.action == 'createfile'}
          {$lang.galleries.files.title.create}
        {else}
          {$lang.galleries.files.title.update}
        {/if}
      </h1>
    </div>
    {if $_REQUEST.action == 'createfile'}
      <div class='control-group{if isset($error.file)} alert alert-error{/if}'>
        <label for='input-file' class='control-label'>
          {$lang.galleries.files.label.choose} <span title="{$lang.global.required}">*</span><br />
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
          <input class='span4 required'
                 type='file'
                 name='file[]'
                 id='input-file'
                 multiple required />
          {if isset($error.file)}<span class='help-inlin'>{$error.file}</span>{/if}
        </div>
      </div>
      <div class='control-group'>
        <label for='input-cut' class='control-label'>
          {$lang.global.cut} <span title='{$lang.global.required}'>*</span>
        </label>
        <div class='controls'>
          <label class='radio'>
            <input type='radio'
                   value='c'
                   name='{$_REQUEST.controller}[cut]'
                   {if !$REQUEST.cut || ($_REQUEST.cut && 'c' == $_REQUEST.cut)}
                      checked='checked'
                   {/if} />
            {$lang.galleries.files.label.cut}
          </label>
          <label class='radio'>
            <input type='radio'
                   value='r'
                   name='{$_REQUEST.controller}[cut]'
                   {if $_REQUEST.cut && 'r' == $_REQUEST.cut}
                      checked='checked'
                   {/if} />
            {$lang.galleries.files.label.resize}
          </label>
        </div>
      </div>
    {/if}
    <div class='control-group'>
      <label for='input-content' class='control-label'>
        {$lang.global.description}
      </label>
      <div class='controls'>
        <input class='span4'
               type='text'
               name='{$_REQUEST.controller}[content]'
               id='input-content'
               value="{$content}" />
        <span class='help-inline'></span>
      </div>
    </div>
    <div class='form-actions'>
      {if $_REQUEST.action == 'createfile'}
        <input type='submit'
               class='btn btn-primary'
               value='{$lang.galleries.files.title.create}' />
      {elseif $_REQUEST.action == 'updatefile'}
        <input type='submit'
               class='btn btn-primary'
               value='{$lang.galleries.files.title.update}' />
        <input type='button'
               value='{$lang.global.destroy.destroy}'
               class='btn btn-danger'
               onclick="confirmDestroy('/{$_REQUEST.controller}/{$_REQUEST.id}/destroyfile')" />
        <input class='btn'
               type='reset'
               value='{$lang.global.reset}' />
      {/if}
    </div>
  </form>
  <script type='text/javascript'>
    $('#input-content').bind('keyup', function() {
      countCharLength(this, 160);
    });

    $("input[type='submit']").click(function() {
      $(this).val(lang.loading);
    });

    $('#input-file').change(function() {
      checkFileSize($(this),
        {$_SYSTEM.maximumUploadSize.raw},
        '{$_SYSTEM.maximumUploadSize.mb|string_format: $lang.error.file.size}');
    });
  </script>
{/strip}