{strip}
  <div itemscope itemtype='http://schema.org/Blog'>
    {if $_SESSION.user.role >= 3}
      <p class='center'>
        <a href='/{$_REQUEST.controller}/create'>
          <i class='icon-plus'
            title='{$lang.global.create.entry}'></i>
          {$lang.global.create.entry}
        </a>
      </p>
    {/if}
    {if !$blogs}
      <div class='alert alert-warning'>
        <h4>{$lang.error.missing.entries}</h4>
      </div>
    {else}
      {foreach $blogs as $b}
        <article class='blogs' itemscope itemtype='http://schema.org/BlogPosting'>
          <header class='page-header'>
            <h2 itemprop='headline'>
              {if !$b.published}
                {$lang.global.not_published}:&nbsp;
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
              {if $_SESSION.user.role >= 3}
                <img src='{$_PATH.img.core}/candy.flags/{$b.language}.png'
                    alt='{$b.language}'
                    title='{$b.language}' />
                &nbsp;
              {/if}
              <time datetime='{$b.date.w3c}'
                    class='js-timeago'
                    itemprop='dateCreated'>
                {$b.date.raw|date_format:$lang.global.time.format.datetime}
              </time>
              &nbsp;
              {$lang.global.by}
              &nbsp;
              <a href='{$b.author.url}'
                 rel='author'
                 itemprop='author'>{$b.author.full_name}</a>
              {if $b.date_modified.raw}
                &nbsp;
                - {$lang.global.last_update}:
                &nbsp;
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
            {if !$DISABLE_COMMENTS && !preg_match('/Disqus/', $ALLOW_PLUGINS)}
              <div class='span4 comments'>
                <a href='{$b.url}#comments'
                   class='pull-right'
                   itemprop='discussionUrl'>
                    {$b.comment_count} {$lang.global.comments}
                </a>
              </div>
              <meta itemprop='interactionCount' content='Comments:{$b.comment_count}' />
            {/if}
            {if isset($_REQUEST.id)}
              <div class='span8'>
                <hr />
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
      {if isset($_REQUEST.id) && preg_match('/Disqus/', $ALLOW_PLUGINS)}
        <!-- plugin:disqus -->
      {elseif isset($_REQUEST.id) && !$DISABLE_COMMENTS}
        {$_comments_}
      {/if}
    {/if}
    <script src='{$_PATH.js.core}/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
    <script type='text/javascript'>
      $(document).ready(function(){
        $('.js-fancybox').fancybox({
          nextEffect : 'fade',
          prevEffect : 'fade'
        });

        $('.js-media').each(function(e) {
          var $this = $(this);
          $.getJSON(this.title, function(data) {
            $this.html(data['html']);
          });
        });
      });
    </script>
  </div>
{/strip}
