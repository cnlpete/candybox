{if $_SESSION.user.role >= 3}
  <p class='center'>
    <a href='#'>
      <i class='icon-plus'
         title='{$lang.global.create.entry}'></i>
      {$lang.galleries.files.title.create}
    </a>
  </p>
{/if}
{if !$album.title}
  <div class='alert alert-warning'>
    <h4>{$lang.error.missing.entry}</h4>
  </div>
{else}
  <article itemscope itemtype='http://schema.org/ImageGallery'>
    <header class='page-header'>
      <h1 itemprop='name'>
        {if !$album.published}
          {$lang.global.not_published}:
        {/if}
        {$album.title}
        {if $_SESSION.user.role >= 3}
          <a href='/{$_REQUEST.controller}/{$_REQUEST.id}/update'>
            <i class='icon-pencil js-tooltip'
              title='{$lang.global.update.update}'></i>
          </a>
        {/if}
        <small>
          ({$album.files_sum} {$lang.global.files})
        </small>
        <meta itemprop='interactionCount' content='Albums:{$album.files_sum}' />
      </h1>
    </header>
    {if $album.content}
      <p itemprop='text'>{$album.content}</p>
    {/if}
    {if !$album.files}
      <div class='alert alert-warning'>
        <h4>{$lang.error.missing.files}</h4>
      </div>
    {else}
      <ul class='thumbnails'>
        {foreach $album.files as $f}
          <li id='file_{$f.id}'>
            <a href='{$f.url_popup}'
                class='thumbnail js-fancybox fancybox-thumb'
                rel='fancybox-thumb'
                title='{$f.content}'
                data-fancybox-group='thumb'>
              <img src='{$f.url_thumb}'
                    alt='{$f.file}'
                    title=''
                    class='js-image'
                    itemprop='thumbnailUrl'/>
            </a>
            {if $_SESSION.user.role >= 3}
              <p class='center'>
                <a href='{$f.url_update}'>
                  <i class='icon-pencil js-tooltip'
                      title='{$lang.global.update.update}'></i>
                </a>
                <i class='icon-trash js-tooltip'
                  onclick="confirmDestroy('{$f.url_destroy}', 'file_{$f.id}')"
                  title='{$lang.global.destroy.destroy}'></i>
              </p>
            {/if}
          </li>
        {/foreach}
      </ul>
      {* This div is needed for sortable actions *}
      <div class='form-actions hide'>
        <input id='js-update-order' type='button'
              class='btn btn-primary'
              value='{$lang.galleries.files.update.order}' />
      </div>
    {/if}
    <p class='center'>
      <a href='{$album.url_clean}.rss'>
        <i class='icon-rss js-tooltip'
            title='{$lang.global.rss}'></i>
      </a>
    </p>
  </article>
{/if}
{if $_SESSION.user.role >= 3}
  <script type='text/javascript' src='{$_PATH.js.core}/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    $(document).ready(function(){
      $('.thumbnails').sortable({
        update : function () {
          $('#js-update-order').parent().fadeIn();
        }
      });

      $('#js-update-order').click(function() {
        $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/updateorder',
          $('.thumbnails').sortable('serialize'),
          function(data) {
            if(data.success == true)
              $('#js-update-order').parent().fadeOut();
          }, 'json');
      });

      showAjaxUpload('js-gallery_upload', '{$_REQUEST.controller}', '{$_REQUEST.id}/createfile');
    });
  </script>
{else}
  <script type='text/javascript' src='{$_PATH.js.core}/jquery.masonry{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript'>
    $(document).ready(function(){
      $('.thumbnails').masonry({
        itemSelector: 'li'
      });
    });
  </script>
{/if}
<script type='text/javascript' src='{$_PATH.js.core}/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js'></script>
<script type='text/javascript' src='{$_PATH.js.core}/jquery.fancybox-thumbs{$_SYSTEM.compress_files_suffix}.js'></script>
<script type='text/javascript'>
  $(document).ready(function(){
    $('.js-fancybox').fancybox({
      nextEffect : 'fade',
      prevEffect : 'fade',
      helpers	: {
        thumbs	: {
          width	: 80,
          height	: 80
        }
      }
    });
  });
</script>