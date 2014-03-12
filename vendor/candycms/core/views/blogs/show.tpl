<div itemscope itemtype='http://schema.org/Blog'>
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/create'>
        <i  class='icon-plus'
            title='{$lang.global.create.entry}'></i>
        {$lang.global.create.entry}
      </a>
    </p>
  {/if}

  {if !$blogs}
    <div class='alert alert-warning'>
      <p>{$lang.error.missing.entries}</p>
    </div>
  {else}
    {foreach $blogs as $b}
      <article class='blogs {if $b.sticky}sticky{/if}' itemscope itemtype='http://schema.org/BlogPosting'>
        <header class='page-header'>
          <h2 itemprop='headline'>
            {if $b.sticky}
              <i class='icon-pin' title='{$lang.global.priority}'></i>
            {/if}
            {if !$b.published}
              {$lang.global.not_published}:
            {/if}
            <a href='{$b.url}'
               itemprop='discussionUrl'>
              {$b.title}
            </a>
            {if $_SESSION.user.role >= 3}
              <a href='{$b.url_update}'>
                <i class='icon-pencil js-tooltip'
                  title='{$lang.global.update.update}'></i>
              </a>
            {/if}
          </h2>
          <p>
            {if $_SESSION.user.role >= 3 && $b.language}
              <img src='{$_PATH.img.core}/candy.flags/{$b.language}.png'
                  alt='{$b.language}'
                  title='{$b.language}' />
            {/if}
            <time datetime='{$b.date.w3c}'
                  class='js-timeago'
                  itemprop='dateCreated'>
              {$b.date.raw|date_format:$lang.global.time.format.datetime}
            </time>
            {$lang.global.by}
            <a href='{$b.author.url}'
               rel='author'
               itemprop='author'>{$b.author.full_name}</a>
            {if $b.date_modified.raw}
              - {$lang.global.last_update}:
              <time datetime='{$b.date_modified.w3c}'
                    class='js-timeago'
                    itemprop='dateModified'>
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
        <div itemprop='text'>
          {$b.content}
        </div>
        <footer class='row'>
          <div class='span4 tags'>
            {if $b.tags|@count > 0}
              {$lang.global.tags.tags}:
              {foreach $b.tags as $t}
                <a class='js-tooltip' title='{$lang.global.tags.info}: {$t}' href='/{$_REQUEST.controller}/{$t}'>
                  <span itemprop='keywords'>{$t}</span>
                </a>
                {if !$t@last}, {/if}
              {/foreach}
            {else}
              &nbsp;
            {/if}
          </div>
          {if isset($_REQUEST.id)}
            <div class='span8'>
              <!-- plugin:addthis -->
              <!-- plugin:socialshareprivacy -->
            </div>
          {/if}
        </footer>
      </article>
    {/foreach}
    {if !isset($_REQUEST.id)}
      {$_pagination_}
    {/if}
    {if isset($_REQUEST.id)}
      <!-- pluginmanager:comment -->
    {/if}
  {/if}
  <script src='{$_PATH.js.core}/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script type='text/javascript'>
    $(document).ready(function(){
      $('.js-fancybox').fancybox({
        nextEffect : 'fade',
        prevEffect : 'fade'
      });

      $('.js-media').each(function() {
        var $this = $(this);
        $.getJSON(this.title, function(data) {
          $this.html(data.html);
        });
      });
    });
  </script>
</div>
