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
    <h1>{$lang.global.contents}</h1>
  </div>
  <table class='table'>
    <thead>
      <tr>
        <th class='column-id headerSortDown'>#</th>
        <th class='column-title'>{$lang.global.title}</th>
        <th class='column-date'>{$lang.global.date.date}</th>
        <th>{$lang.global.author}</th>
        {if $_SESSION.user.role >= 3}
          <th class='column-published center'>{$lang.global.published}</th>
          <th class='column-actions'></th>
        {/if}
      </tr>
    </thead>
    {foreach $contents as $c}
      <tr id='row_{$c.id}'>
        <td>{$c.id}</td>
        <td>
          <a href='{$c.url}'>
            {$c.title}
          </a>
        </td>
        <td>
          <time datetime='{$c.date.w3c}' class='js-timeago'>
            {$c.date.raw|date_format:$lang.global.time.format.datetime}
          </time>
        </td>
        <td>
          <a href='{$c.author.url}'>
            {$c.author.full_name}
          </a>
        </td>
        {if $_SESSION.user.role >= 3}
          <td class='center'>
            <i class='icon-{if $c.published == true}ok{/if} js-tooltip'
               title='{if $c.published == true}✔{/if}'></i>
          </td>
          <td>
            <a href='{$c.url_update}'>
              <i class='icon-pencil js-tooltip'
                 title='{$lang.global.update.update}'></i>
            </a>
            &nbsp;
            <i class='icon-trash js-tooltip'
               onclick="confirmDestroy('{$c.url_destroy}', 'row_{$c.id}')"
               title='{$lang.global.destroy.destroy}'></i>
          </td>
        {/if}
      </tr>
    {/foreach}
  </table>
  {$_pages_}
  <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.infiniteScroll{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    {if $_AUTOLOAD.enabled}
      $(document).ready(function(){
        enableInfiniteScroll('table', 'table tbody tr', {$_AUTOLOAD.times}, '{$_PATH.core}/assets/images');
      });
    {/if}
    $('table').tablesorter();
  </script>
{/strip}