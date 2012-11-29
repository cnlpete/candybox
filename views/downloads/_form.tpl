{strip}
  {if $_REQUEST.action == 'update'}
    <div class='page-header'>
      <h1>{$lang.downloads.title.update}</h1>
    </div>
  {/if}
  <form method='post' class='form-horizontal'>
    {if $_REQUEST.action == 'create'}
      <div class='control-group{if isset($error.file)} alert alert-error{/if}'>
        <label for='input-file' class='control-label'>
          {$lang.downloads.label.choose} <span title='{$lang.global.required}'>*</span><br />
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
          {* @todo: Rename file *}
          <input class='input-file span4 required'
                 type='file'
                 name='file[]'
                 id='input-file'
                 required />
        </div>
      </div>
    {/if}
    <div class='control-group{if isset($error.title)} alert alert-error{/if}'>
      <label for='input-title' class='control-label'>
        {$lang.global.title} <span title='{$lang.global.required}'>*</span>
      </label>
      <div class='controls'>
        <input class='span4 required'
               type='text'
               name='{$_REQUEST.controller}[title]'
               id='input-title'
               value="{$title}"
               required />
        <span class='help-inline'>
          {if isset($error.title)}
            {$error.title}
          {/if}
        </span>
      </div>
    </div>
    <div class='control-group{if isset($error.category)} alert alert-error{/if}'>
      <label for='input-category' class='control-label'>
        {$lang.global.category} <span title='{$lang.global.required}'>*</span>
      </label>
      <div class='controls'>
        <input type='text'
               name='{$_REQUEST.controller}[category]'
               id='input-category'
               placeholder=''
               data-provide='typeahead'
               value="{$category}"
               data-source='{$_categories_}'
               data-items='8'
               class='span4 required'
               autocomplete='off'
               required />
        {if isset($error.category)}
          <span class='help-inline'>
            {$error.category}
          </span>
        {/if}
      </div>
    </div>
    <div class='control-group{if isset($error.content)} alert alert-error{/if}'>
      <label for='input-content' class='control-label'>
        {$lang.global.description}
      </label>
      <div class='controls'>
        <input class='span4'
               type='text'
               name='{$_REQUEST.controller}[content]'
               id='input-content'
              value="{$content}" />
        {if isset($error.content)}
          <span class='help-inline'>
            {$error.content}
          </span>
        {/if}
      </div>
    </div>
    {if $_REQUEST.action == 'update'}
      <div class='control-group'>
        <label for='input-downloads' class='control-label'>
          {$lang.global.downloads}
        </label>
        <div class='controls'>
          <input class='span4 required'
                 type='text'
                 name='{$_REQUEST.controller}[downloads]'
                 id='input-downloads'
                 value='{$downloads}' />
        </div>
      </div>
    {/if}
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
      {if $_REQUEST.action == 'create'}
        <input type='button'
               id='js-submit_download'
               class='btn btn-primary'
               value='{$lang.global.create.create}' />
      {elseif $_REQUEST.action == 'update'}
        <input type='submit'
               class='btn btn-primary'
               value='{$lang.global.update.update}' />
        <input type='button'
               class='btn btn-danger'
               value='{$lang.global.destroy.destroy}'
               onclick="confirmDestroy('/{$_REQUEST.controller}/{$_REQUEST.id}/destroy')" />
        <input type='reset'
               class='btn'
               value='{$lang.global.reset}' />
        <input type='hidden'
               name='method'
               value='PUT' />
      {/if}
    </div>
  </form>
  <script type='text/javascript' src='{$_PATH.js}/core/jquery.bootstrap.typeahead{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    $(document).ready(function(){
      $('#input-title').bind('keyup', function() {
        countCharLength(this, 128);
      });

      $('#input-file').change(function() {
        checkFileSize($(this), {$_SYSTEM.maximumUploadSize.raw}, '{$_SYSTEM.maximumUploadSize.mb|string_format: $lang.error.file.size}');
        prepareForUpload();
      });

      $('#js-submit_download').click(function() {
        upload(this, '{$_REQUEST.controller}', '{$_REQUEST.controller}', 'file', 'title', true);
      });
    });
  </script>
{/strip}