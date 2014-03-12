<div class='page-header'>
  <h1>{$lang.global.mails}</h1>
</div>
{if !$mails}
  <div class='alert alert-warning'>
    <h4>{$lang.error.missing.entries}</h4>
  </div>
{else}
  <table class='table'>
    <thead>
      <tr>
        <th class='column-from'>{$lang.global.author}</th>
        <th class='column-to'>{$lang.global.receiver}</th>
        <th class='column-subject'>{$lang.global.subject}</th>
        <th class='column-date headerSortDown'>{$lang.global.date.date}</th>
        <th class='column-actions'></th>
      </tr>
    </thead>
    {foreach $mails as $m}
      <tr class='js-tooltip' title='{$m.error_message}' id='row_{$m.id}'>
        <td class='left'>
          <a href='mailto:{$m.from_address}'>
            {if !empty($m.from_name)}
              {$m.from_name}
            {else}
              {$m.from_address}
            {/if}
          </a>
        </td>
        <td>
          <a href='mailto:{$m.to_address}'>
            {if !empty($m.to_name)}
              {$m.to_name}
            {else}
              {$m.to_address}
            {/if}
          </a>
        </td>
        <td>
          {$m.subject}
        </td>
        <td>
          <time datetime='{$m.date.w3c}' class='js-timeago'>
            {$m.date.raw|date_format:$lang.global.time.format.datetime}
          </time>
        </td>
        <td class='center'>
          <i class='icon-mail'
             title='{$lang.global.email.send}'
             data-id='{$m.id}'></i>
        </td>
      </tr>
    {/foreach}
  </table>
  <script type='text/javascript' src='{$_PATH.js.core}/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript' src='{$_PATH.js.core}/jquery.tablesorter{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    $('table').tablesorter();

    $('.icon-mail').click(function() {
      /* @todo improve code */
      var iId = $('.icon-mail').data('id');

      $.getJSON('/mails/' + iId + '/resend.json', function(data) {
        if (data.success === true) {
          $('#row_' + iId).effect("highlight", {
            mode: 'hide'
          }, 2000);
        }
        else {
          $('#' + iId).addClass('result-error');
        }
      });
    });
  </script>
{/if}