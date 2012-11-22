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
          <tr id='row_{$f@index}'>
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
              <i class='icon-trash js-tooltip'
                 onclick="confirmDestroy('{$f.url_destroy}', 'row_{$f@index}')"
                 title='{$lang.global.destroy.destroy}'></i>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/if}
  <script type='text/javascript' src='{$_PATH.js}/core/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript' src='{$_PATH.js}/core/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript' src='{$_PATH.js}/core/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type="text/javascript">
    $(document).ready(function(){
      $(".js-fancybox").fancybox({
        nextEffect : 'fade',
        prevEffect : 'fade' });

      $('table').tablesorter();

      $('p.center ').click(function(e) {
        if($('#js-media_upload').length == 0) {
          $('.page-header').after("<div id='js-media_upload'></div>");
          $('#js-media_upload').load('/{$_REQUEST.controller}/create.ajax');
        }
        else {
          $('.form-horizontal').toggle();
        }
      });
    });
  </script>
{/strip}