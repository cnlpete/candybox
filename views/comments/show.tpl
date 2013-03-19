{strip}
  {if isset($comments) && count($comments) > 0}
    <div id='comments' itemscope itemtype='http://schema.org/CreativeWork'>
      <div class='page-header'>
        <a name='comments'></a>
        <h2 itemprop='headline'>
          {$lang.global.comments}
        </h2>
      </div>
      <div id='js-commments'>
        {foreach $comments as $c}
          <article itemscope itemtype='http://schema.org/Comments'{if $c.author.id == $author_id} class='from_author'{/if} id='comment_{$c.id}'>
            <header>
              <a href='#{$c.id}'
                 name='{$c.id}'
                 class='count'>
                {$c.loop+$comment_number}
              </a>
              <img class='thumbnail'
                   src='{$c.author.avatar_64}'
                   width='40'
                   height='40'
                   alt='{$c.author.name}' />
              {if $c.author.id > 0}
                <a href='{$c.author.url}'
                   rel='author'
                   itemprop='creator'>
                  {$c.author.full_name}
                </a>
              {elseif $c.author.full_name}
                <span itemprop='creator'>
                  {$c.author.full_name}
                </span>
              {else}
                <em style='text-decoration:line-through'
                    itemprop='creator'>
                  {$lang.global.deleted_user}
                </em>
              {/if}
              <br />
              <time datetime='{$c.date.w3c}'
                    class='js-timeago'
                    itemprop='dateCreated'>
                {$c.date.raw|date_format:$lang.global.time.format.datetime}
              </time>
            </header>
            <div id='js-comment_{$c.id}'
                 itemprop='text'>
              {$c.content}
            </div>
            <footer>
              {if $_SESSION.user.role >= 3 && $c.author.email}
                <a href='mailto:{$c.author.email}'>
                  {$c.author.email}
                </a>
                &nbsp;
              {/if}
              {if $_SESSION.user.role >= 3 && $c.author.ip}
                <span>{$c.author.ip}</span>
                &nbsp;
              {/if}
              <a href='#create' rel='nofollow'
                 onclick="quote('{$c.author.full_name}', 'js-comment_{$c.id}')">
                <i class='icon-comment js-tooltip'
                   title='{$lang.global.quote.quote}'></i>
              </a>
              {if $_SESSION.user.role >= 3}
                &nbsp;
                <i class='icon-trash js-tooltip'
                   onclick="confirmDestroy('{$c.url_destroy}', 'comment_{$c.id}')"
                   title='{$lang.global.destroy.destroy}'></i>
              {/if}
            </footer>
          </article>
        {/foreach}
      </div>
    </div>
    {$_pagination_}
    <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
    {if $_AUTOLOAD.enabled && isset($comments) && count($comments) > 0}
      <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.infiniteScroll{$_SYSTEM.compress_files_suffix}.js'></script>
      <script type='text/javascript'>
        $(document).ready(function(){
          enableInfiniteScroll('#js-commments', '#js-commments article', {$_AUTOLOAD.times}, '{$_PATH.core}/assets/images');
        });
      </script>
    {/if}
  {/if}
{/strip}