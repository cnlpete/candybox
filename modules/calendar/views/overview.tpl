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
  <h1>
    {$lang.global.calendar}
    {if isset($_REQUEST.action) && $_REQUEST.action == 'archive'}
      -
      {$lang.global.archive}
    {else}
      <a id='test-icalfeedlink' href='/{$_REQUEST.controller}/icalfeed'>
        <i class='icon-calendar js-tooltip'
           title='{$lang.calendars.info.icsfeed}'></i>
      </a>
    {/if}
  </h1>
</div>

{if isset($_REQUEST.id)}
  <p class='center'>
    <a href='/{$_REQUEST.controller}/{$_REQUEST.id - 1}/archive' rel='prev'>
      &laquo; {$_REQUEST.id - 1}
    </a>
    <strong>{$_REQUEST.id}</strong>
    <a href='/{$_REQUEST.controller}/{$_REQUEST.id + 1}/archive' rel='next'>
      {$_REQUEST.id + 1} &raquo;
    </a>
  </p>
{/if}

{if !$calendar}
  <div class='alert alert-warning'>
    <p>{$lang.error.missing.entries}</p>
  </div>
{else}
  {foreach $calendar as $c}
    <h2>{$c.month} {$c.year}</h2>
    <table class='table tablesorter'>
      <thead>
        <tr>
          <th class='column-date headerSortDown'>
            {$lang.global.date.date}
          </th>
          <th class='column-description'>
            {$lang.global.description}
          </th>
          <th class='column-actions'></th>
        </tr>
      </thead>
      <tbody>
        {foreach $c.dates as $d}
          <tr id='row_{$d.id}' itemscope itemtype='http://schema.org/Event'>
            <td>
              <time datetime='{$d.start_date.w3c_date}' class='js-timeago' itemprop='startDate'>
                {$d.start_date.raw|date_format:$lang.global.time.format.date}
              </time>
              {if $d.end_date.raw}
                -
                <time datetime='{$d.end_date.w3c_date}' class='js-timeago' itemprop='endDate'>
                  {$d.end_date.raw|date_format:$lang.global.time.format.date}
                </time>
              {/if}
            </td>
            <td>
              <strong itemprop='name'>
                {$d.title}
              </strong>
              {if $d.content}
                <br />
                <span itemprop='description'>
                  {$d.content}
                </span>
              {/if}
            </td>
            <td class='right'>
              <a href='{$d.url}'>
                <i class='icon-calendar js-tooltip'
                  title='{$lang.calendars.info.ics}'></i>
              </a>
              {if $_SESSION.user.role >= 3}
                <a href='{$d.url_update}'>
                  <i class='icon-pencil js-tooltip'
                    title='{$lang.global.update.update}'></i>
                </a>
                <i class='icon-trash js-tooltip'
                  onclick="confirmDestroy('{$d.url_destroy}', 'row_{$d.id}')"
                  title='{$lang.global.destroy.destroy}'></i>
              {/if}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/foreach}
{/if}
{if !isset($_REQUEST.action)}
  <p class='center'>
    <a href='/{$_REQUEST.controller}/{$smarty.now|date_format:'%Y'}/archive' class='btn'>
      {$lang.global.archive}
    </a>
  </p>
{/if}
<script type='text/javascript' src='{$_PATH.js.core}/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
<script type='text/javascript' src='{$_PATH.js.core}/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
<script type='text/javascript'>
  $('table').tablesorter();
</script>