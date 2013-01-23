{strip}
  <article class='contents'>
    <header class='page-header'>
      <h1 itemprop='headline'>
        {if $contents.published == false}
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
        <time datetime='{$contents.date.w3c}' class='js-timeago'>
          {$contents.date.raw|date_format:$lang.global.time.format.datetime}
        </time>
        &nbsp;
        {$lang.global.by}
        &nbsp;
        <a href='{$contents.author.url}' rel='author'>
          {$contents.author.full_name}
        </a>
      </p>
    </header>
    {if $contents.teaser}
      <p class='summary'>{$contents.teaser}</p>
    {/if}
    {$contents.content}
    <footer>
      {if $_REQUEST.id && (!isset($_REQUEST.action) || $_REQUEST.action !== 'page')}
        <hr />
        <!-- plugin:addthis -->
        <!-- plugin:socialshareprivacy -->
      {/if}
    </footer>
  </article>
  <script src='{$_PATH.core}/assets/javascripts/core/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script src='{$_PATH.core}/assets/javascripts/core/jquery.capty{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
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