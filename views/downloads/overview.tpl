{strip}
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='#'>
        <i class='icon-plus'
           title='{$lang.global.create.entry}'></i>
        {$lang.global.create.entry}
      </a>
    </p>
  {/if}
  <div class='page-header'>
    <h1>{$lang.global.downloads}</h1>
  </div>
  {if !$downloads}
    <div class='alert alert-warning'>
      <h4>{$lang.error.missing.entries}</h4>
    </div>
  {else}
    {foreach $downloads as $d}
      <h2>{$d.category}</h2>
      <table class='table tablesorter'>
        <thead>
          <tr>
            <th class='column-file'></th>
            <th class='column-title headerSortDown'>{$lang.global.title}</th>
            <th class='column-date'>{$lang.global.date.date}</th>
            <th class='column-size'>{$lang.global.size}</th>
            <th class='column-actions'></th>
          </tr>
        </thead>
        <tbody>
        {foreach $d.files as $f}
          <tr id='row_{$f.id}' itemscope itemtype='http://schema.org/SoftwareApplication'>
            <td class='center'>
              <img src='{$_PATH.img.core}/candy.files/{$f.extension}.png'
                   width='32'
                   height='32'
                   alt='{$f.extension}' />
            </td>
            <td class='left'>
              <a href='{$f.url}'
                 target='_blank'
                 itemprop='downloadUrl'>
                <span itemprop='name'>
                  {$f.title}
                </span>
              </a>
              {if $f.content}
                <br />
                <div itemprop='text'>
                  {$f.content}
                </div>
              {/if}
            </td>
            <td>
              <time datetime='{$f.date.w3c}'
                    class='js-timeago'
                    itemprop='dateCreated'>
                {$f.date.raw|date_format:$lang.global.time.format.date}
              </time>
            </td>
            <td>
              <span itemprop='fileSize'>
                {$f.size}
              </span>
              {if $_SESSION.user.role >= 3}
                <br />
                {$f.downloads} {$lang.global.downloads}
              {/if}
              <meta itemprop='interactionCount' content='Downloads:{$f.downloads}' />
            </td>
            <td class='center'>
              {if $_SESSION.user.role >= 3}
                <a href='{$f.url_update}'>
                  <i class='icon-pencil js-tooltip'
                     title='{$lang.global.update.update}'></i>
                </a>
                &nbsp;
                <i class='icon-trash js-tooltip'
                  onclick="confirmDestroy('{$f.url_destroy}', 'row_{$f.id}')"
                  title='{$lang.global.destroy.destroy}'></i>
              {else}
                <a href='{$f.url}'
                   itemprop='downloadUrl'>
                  <i class='icon-download js-tooltip'
                     title='{$lang.global.download}'></i>
                </a>
              {/if}
            </td>
          </tr>
        {/foreach}
        </tbody>
      </table>
    {/foreach}
  {/if}
  {if $_SESSION.user.role >= 3}
    <script type='text/javascript' src='{$_PATH.js.core}/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
  {/if}
  <script type='text/javascript' src='{$_PATH.js.core}/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    $(document).ready(function(){
      $('table').tablesorter();
      showAjaxUpload('js-download_upload', '{$_REQUEST.controller}', 'create');
    });
  </script>
{/strip}