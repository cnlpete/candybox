{strip}
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/create'>
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
          <tr>
            <td class='center'>
              <img src='{$_PATH.images}/candy.files/{$f.extension}.png'
                  width='32' height='32' alt='{$f.extension}' />
            </td>
            <td class='left'>
              <a href='{$f.url}' target='_blank'>{$f.title}</a>
              {if $f.content}
                <br />
                {$f.content}
              {/if}
            </td>
            <td>
              <time datetime='{$f.date.w3c}' class='js-timeago'>
                {$f.date.raw|date_format:$lang.global.time.format.date}
              </time>
            </td>
            <td>
              {$f.size}
              {if $_SESSION.user.role >= 3}
                <br />
                {$f.downloads} {$lang.global.downloads}
              {/if}
            </td>
            <td class='center'>
              {if $_SESSION.user.role >= 3}
                <a href='{$f.url_update}'>
                  <i class='icon-pencil js-tooltip'
                     title='{$lang.global.update.update}'></i>
                </a>
                &nbsp;
                <a href="#" onclick="confirmDestroy('{$f.url_destroy}')">
                  <i class='icon-trash js-tooltip'
                     title='{$lang.global.destroy.destroy}'></i>
                </a>
              {else}
                <a href='{$f.url}'>
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
  <script type='text/javascript' src='{$_PATH.js}/core/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    $('table').tablesorter();
  </script>
{/strip}