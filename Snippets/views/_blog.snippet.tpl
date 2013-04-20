{strip}
  <div itemscope itemtype='http://schema.org/Blog'>
    {if !$blogs}
      <div class='alert alert-warning'>
        <h4>{$lang.error.missing.entries}</h4>
      </div>
    {else}
      {foreach $blogs as $b}
        <article class='blogs' itemscope itemtype='http://schema.org/BlogPosting'>
          <header class='page-header'>
            <h2 itemprop='headline'>
              <a href='{$b.url}' itemprop='discussionUrl'>
                {$b.title}
              </a>
            </h2>
            <p>
              <time datetime='{$b.date.w3c}' class='js-timeago' itemprop='dateCreated'>
                {$b.date.raw|date_format:$lang.global.time.format.datetime}
              </time>
              &nbsp; {$lang.global.by} &nbsp;
              <a href='{$b.author.url}' rel='author' itemprop='author'>{$b.author.full_name}</a>
              {if $b.date_modified.raw}
                &nbsp; - {$lang.global.last_update}: &nbsp;
                <time datetime='{$b.date_modified.w3c}' class='js-timeago' itemprop='dateModified'>
                  {$b.date_modified.raw|date_format:$lang.global.time.format.datetime}
                </time>
              {/if}
            </p>
          </header>
          {if $b.teaser}
            <p class='summary' itemprop='description'>
              {$b.teaser}
            </p>
          {/if}
        </article>
      {/foreach}
    {/if}
  </div>
{/strip}
