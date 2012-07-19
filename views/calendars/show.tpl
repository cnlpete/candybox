{strip}
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/create'>
        <span class='icon-plus'
              title='{$lang.global.create.entry}'></span>
        {$lang.global.create.entry}
      </a>
    </p>
  {/if}
  <div class='page-header'>
    <h1>
      {$lang.global.calendar}
      {if isset($_REQUEST.action) && $_REQUEST.action == 'archive'}
        &nbsp;
        -
        &nbsp;
        {$lang.global.archive}
      {else}
        &nbsp;
        <a id='test-icalfeedlink' href='/{$_REQUEST.controller}/icalfeed'>
          <span class='icon-calendar js-tooltip'
                title='{$lang.calendars.info.icsfeed}'></span>
        </a>
      {/if}
    </h1>
  </div>
  {if isset($_REQUEST.id)}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/{$_REQUEST.id - 1}/archive' rel='prev'>
        &laquo; {$_REQUEST.id - 1}
      </a>
      &nbsp;&nbsp;
      <strong>{$_REQUEST.id}</strong>
      &nbsp;&nbsp;
      <a href='/{$_REQUEST.controller}/{$_REQUEST.id + 1}/archive' rel='next'>
        {$_REQUEST.id + 1} &raquo;
      </a>
    </p>
  {/if}
  {if !$calendar}
    <div class='alert alert-warning'>
      <h4>{$lang.error.missing.entries}</h4>
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
            <tr>
              <td>
                <time datetime='{$d.start_date.w3c_date}' class='js-timeago'>
                  {$d.start_date.raw|date_format:$lang.global.time.format.date}
                </time>
                {if $d.end_date.raw}
                  &nbsp;-&nbsp;
                  <time datetime='{$d.end_date.w3c_date}' class='js-timeago'>
                    {$d.end_date.raw|date_format:$lang.global.time.format.date}
                  </time>
                {/if}
              </td>
              <td>
                <strong>
                  {$d.title}
                </strong>
                {if $d.content}
                  <br />
                  {$d.content}
                {/if}
              </td>
              <td class='center'>
                <a href='{$d.url}'>
                  <span class='icon-calendar js-tooltip'
                        title='{$lang.calendars.info.ics}'></span>
                </a>
                {if $_SESSION.user.role >= 3}
                  &nbsp;
                  <a href='/{$_REQUEST.controller}/{$d.id}/update'>
                    <span class='icon-pencil js-tooltip'
                          title='{$lang.global.update.update}'></span>
                  </a>
                  &nbsp;
                  <a href="#" onclick="confirmDestroy('/{$_REQUEST.controller}/{$d.id}/destroy')">
                    <span class='icon-trash js-tooltip'
                        title='{$lang.global.destroy.destroy}'></span>
                  </a>
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
  <script type='text/javascript' src='{$_PATH.js}/core/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    $('table').tablesorter();
  </script>
{/strip}