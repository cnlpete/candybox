{strip}
  <article class='contents' itemscope itemtype='http://schema.org/Article'>
    <header class='page-header'>
      <h1 itemprop='headline'>
        {if !$contents.published}
          {$lang.global.not_published}:&nbsp;
        {/if}
        {$contents.title}
        {if $_SESSION.user.role >= 3}
          <a href='{$contents.url_update}'>
            <i class='icon-pencil js-tooltip'
              title='{$lang.global.update.update}'></i>
          </a>
        {/if}
      </h1>
      <p>
        {$lang.global.last_update}:
        &nbsp;
        <time datetime='{$contents.date.w3c}'
              class='js-timeago'
              itemprop='dateCreated'>
          {$contents.date.raw|date_format:$lang.global.time.format.datetime}
        </time>
        &nbsp;
        {$lang.global.by}
        &nbsp;
        <a href='{$contents.author.url}'
           rel='author'
           itemprop='author'>
          {$contents.author.full_name}
        </a>
      </p>
    </header>
    {if $contents.teaser}
      <p class='summary'
         itemprop='description'>
        {$contents.teaser}
      </p>
    {/if}
    <div itemprop='text'>
      {$contents.content}
    </div>
    <footer>
      {if $_REQUEST.id && (!isset($_REQUEST.action) || $_REQUEST.action !== 'page')}
        <hr />
        <!-- plugin:addthis -->
        <!-- plugin:socialshareprivacy -->
      {/if}
    </footer>
  </article>
  <script src='{$_PATH.js.core}/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script type="text/javascript">
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
{/strip}