{strip}
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/create'>
        <img src='{$_PATH.images}/candy.global/spacer.png'
            class='icon-create'
            alt='{$lang.global.create.entry}'
            width='16' height='16' />
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
      <article class='blogs'>
        <header class='page-header'>
          <h2>
            {if $b.published == false}
              {$lang.global.not_published}:&nbsp;
            {/if}
            <a href='{$b.url}'>{$b.title}</a>
            {if $_SESSION.user.role >= 3}
              <a href='{$b.url_update}'>
                <img src='{$_PATH.images}/candy.global/spacer.png'
                    class='icon-update js-tooltip'
                    alt='{$lang.global.update.update}'
                    title='{$lang.global.update.update}'
                    width='16' height='16' />
              </a>
            {/if}
          </h2>
          <p>
            <time datetime='{$b.date.w3c}' class='js-timeago'>
              {$b.date.raw|date_format:$lang.global.time.format.datetime}
            </time>
            &nbsp;
            {$lang.global.by}
            &nbsp;
            <a href='{$b.author.url}' rel='author'>{$b.author.full_name}</a>
            {if $b.date_modified.raw}
              &nbsp;
              - {$lang.global.last_update}: <time datetime='{$b.date_modified.w3c}' class='js-timeago'>
                {$b.date_modified.raw|date_format:$lang.global.time.format.datetime}
              </time>
            {/if}
          </p>
        </header>
        {if $b.teaser}
          <p class='summary'>
            {$b.teaser}
          </p>
        {/if}
        {$b.content}
        <footer class='row'>
          <div class='span4 tags'>
            {if $b.tags|@count > 0}
              {$lang.global.tags.tags}:
              {foreach $b.tags as $t}
                <a class='js-tooltip' title='{$lang.global.tags.info}: {$t}' href='/{$_REQUEST.controller}/{$t}'>
                  {$t}
                </a>
                {if !$t@last}, {/if}
              {/foreach}
            {else}
              &nbsp;
            {/if}
          </div>
          <div class='span4 comments right'>
            <a href='{$b.url}#comments'>
              {$b.comment_sum} {$lang.global.comments}
            </a>
          </div>
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
    {* Show comments only if we got a entry *}
    {if isset($b.id)}
      {$_blog_footer_}
    {/if}
  {/if}
  <script src='{$_PATH.js}/core/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script src='{$_PATH.js}/core/jquery.capty{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script type="text/javascript">
    $(document).ready(function(){
      $('.js-fancybox').fancybox({
        nextEffect : 'fade',
        prevEffect : 'fade'
      });
      $('.js-image').capty({ height: 30 });

      $('.js-media').each(function(e) {
        var $this = $(this);
        $.getJSON(this.title, function(data) {
          $this.html(data['html']);
        });
      });
    });
  </script>
{/strip}