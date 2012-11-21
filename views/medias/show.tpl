{strip}
  <p class='center'>
    <a href='#'>
      <i class='icon-plus'
          title='{$lang.global.create.entry}'></i>
      {$lang.global.create.entry}
    </a>
  </p>
  <div class='page-header'>
    <h1>
      {$lang.global.manager.media}
    </h1>
  </div>
  <div class='form-horizontal hide'>
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
  {if !$files}
    <div class='alert alert-warning'>
      <h4>{$lang.error.missing.files}</h4>
    </div>
  {else}
    <table class='table'>
      <thead>
        <tr>
          <th class='column-icon'></th>
          <th class='column-file headerSortDown'>{$lang.global.file}</th>
          <th class='column-size'>{$lang.global.size}</th>
          <th class='column-uploaded_at center'>{$lang.global.upload.at}</th>
          <th class='column-actions'></th>
        </tr>
      </thead>
      <tbody>
        {foreach $files as $f}
          <tr>
            <td class='center'>
              {if ($f.type == 'jpg' || $f.type == 'jpeg' || $f.type == 'gif' || $f.type == 'png')}
                <img src='{$_PATH.upload}/temp/{$_REQUEST.controller}/{$f.name}'
                    class='thumbnail'
                    width='32' height='32'
                    alt='{$f.type}' />
              {else}
                <img src='{$_PATH.images}/files/{$f.type}.png'
                    class='thumbnail'
                    width='32' height='32'
                    alt='{$f.type}' />
              {/if}
            </td>
            <td>
              {if ($f.type == 'png' || $f.type == 'gif' || $f.type == 'jpg' || $f.type == 'jpeg')}
                <a href='{$_PATH.upload}/{$_REQUEST.controller}/{$f.name}'
                  class='js-fancybox'
                  rel='image'
                  title='{$f.name} - ({$f.dim[0]} x {$f.dim[1]} px)'>
                  {$f.name}
                </a> ({$f.dim[0]} x {$f.dim[1]} px)
              {else}
                <a href='{$_PATH.upload}/{$_REQUEST.controller}/{$f.name}'>
                  {$f.name}
                </a>
              {/if}
              <input type='text'
                    class='copybox'
                    value='{$f.url}'
                    onclick='this.focus();this.select();'
                    readonly />
            </td>
            <td>
              {$f.size}
            </td>
            <td class='center'>
              <time datetime='{$f.date.w3c}' class='js-timeago'>
                {$f.date.raw|date_format:$lang.global.time.format.date}
              </time>
            </td>
            <td>
              <a href="#" onclick="confirmDestroy('{$f.url_destroy}')">
                <i class='icon-trash js-tooltip'
                  title='{$lang.global.destroy.destroy}'></i>
              </a>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/if}
  <script src='{$_PATH.js}/core/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script type='text/javascript' src='{$_PATH.js}/core/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type="text/javascript">
    $(document).ready(function(){
      $(".js-fancybox").fancybox({ nextEffect : 'fade', prevEffect : 'fade' });
    });

    $('table').tablesorter();

    $('#input-file').change(function() {
      checkFileSize($(this), {$_SYSTEM.maximumUploadSize.raw}, '{$_SYSTEM.maximumUploadSize.mb|string_format: $lang.error.file.size}');
      prepareForUpload();
    });

    $("input[type='submit']").click(function() {
      upload(this, '/{$_REQUEST.controller}/create.json', '{$_REQUEST.controller}', 'file', 'rename');
    });

    $('p.center ').click(function(e) {
      $('.form-horizontal').toggle();
    });
  </script>
{/strip}