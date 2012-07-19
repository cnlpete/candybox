{strip}
  <ul class='pager clearfix'>
    {if $_PAGE.previous}
      <li class='previous'>
        <a href='{$_PAGE.url_previous}' rel='prev'>&larr; {$lang.pages.previous}</a>
      </li>
    {/if}
    {if $_PAGE.next && $_PAGE.entries > $_PAGE.limit}
      <li class='next'>
        <a href='{$_PAGE.url_next}' rel='next'>{$lang.pages.next} &rarr;</a>
      </li>
    {/if}
  </ul>
  <p class='center'>
    <a href='/rss/{$_PAGE.controller}'>
      <span class='icon-rss js-tooltip'
            title='{$lang.global.rss}'></span>
    </a>
  </p>
{/strip}