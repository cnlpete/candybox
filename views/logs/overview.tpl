{strip}
  <div class='page-header'>
    <h1>{$lang.global.logs}</h1>
  </div>
  <table class='table'>
    <thead>
      <tr>
        <th class='column-author'>{$lang.global.author}</th>
        <th class='column-section'>{$lang.global.section}</th>
        <th class='column-action'>{$lang.global.action}</th>
        <th class='column-id center'>{$lang.global.id}</th>
        <th class='column-date headerSortDown'>{$lang.global.date.date}</th>
        <th class='column-actions'></th>
      </tr>
    </thead>
    {foreach $logs as $l}
      {if $l.action_name == 'create' || $l.action_name == 'createfile'}
        <tr id='row_{$l.id}'
            class='result-{if $l.result}success{else}error{/if}' style='color:green;'>
      {elseif $l.action_name == 'update' || $l.action_name == 'updatefile'}
        <tr id='row_{$l.id}'
            class='result-{if $l.result}success{else}error{/if}' style='color:blue;'>
      {elseif $l.action_name == 'destroy' || $l.action_name == 'destroyfile'}
        <tr id='row_{$l.id}'
            class='result-{if $l.result}success{else}error{/if}' style='color:red;'>
      {else}
        <tr id='row_{$l.id}'
            class='result-{if $l.result}success{else}error{/if}'>
      {/if}
        <td class='left'>
          <a href='{$l.author.url}'>{$l.author.full_name}</a>
        </td>
        <td>
          {$l.controller_name}
        </td>
        <td>
          {$l.action_name}
        </td>
        <td class='center'>
          {$l.action_id}
        </td>
        <td>
          <time datetime='{$l.time_start.w3c}' class='js-timeago'>
            {$l.time_start.raw|date_format:$lang.global.time.format.datetime}
          </time>
          {if $l.time_start.raw < $l.time_end.raw - 60}
            &nbsp;-&nbsp;
            <time datetime='{$l.time_end.w3c}' class='js-timeago'>
              {$l.time_end.raw|date_format:$lang.global.time.format.time}
            </time>
          {/if}
        </td>
        <td class='center'>
          <i class='icon-trash js-tooltip'
             onclick="confirmDestroy('{$l.url_destroy}', 'row_{$l.id}')"
             title='{$lang.global.destroy.destroy}'></i>
        </td>
      </tr>
    {/foreach}
  </table>
  {$_pages_}
  <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.infiniteScroll{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    {if $_AUTOLOAD_.enabled}
      $(document).ready(function(){
        enableInfiniteScroll('table', 'table tbody tr', {$_AUTOLOAD_.times}, '{$_PATH.assets}/images');
      });
    {/if}
    $('table').tablesorter();
  </script>
{/strip}