<div class='page-header'>
  <h1>{$lang.global.sites}</h1>
</div>
{if !$sites}
  <div class='alert alert-warning'>
    <h4>{$lang.error.missing.entries}</h4>
  </div>
{else}
  <ul>
    {foreach $sites as $s}
      <li>
        <a href='{$s.url}'>
          {$s.title}
        </a>
      </li>
    {/foreach}
  </ul>
{/if}