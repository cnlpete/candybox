{strip}
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/{$_REQUEST.id}/createfile'>
        <span class='icon-plus'
              title='{$lang.global.create.entry}'></span>
        {$lang.galleries.files.title.create}
      </a>
    </p>
  {/if}
  {if !$gallery_name}
    <div class='alert alert-warning'>
      <h4>{$lang.error.missing.entry}</h4>
    </div>
  {else}
    <header class='page-header'>
      <h1>
        {$gallery_name}
        <small>
          ({$file_no} {$lang.global.files})
        </small>
        {if $_SESSION.user.role >= 3}
          <a href='/{$_REQUEST.controller}/{$_REQUEST.id}/update'>
            <span class='icon-pencil js-tooltip'
                  title='{$lang.global.update.update}'></span>
          </a>
        {/if}
      </h1>
    </header>
    {if $gallery_content}
      <p>{$gallery_content}</p>
    {/if}
    {if !$files}
      <div class='alert alert-warning'>
        <h4>{$lang.error.missing.files}</h4>
      </div>
    {else}
      <ul class='thumbnails'>
        {foreach $files as $f}
          <li id='galleryfiles-{$f.id}'>
            <a href='{$f.url_popup}'
               class='thumbnail js-fancybox fancybox-thumb'
               rel='fancybox-thumb'
               title='{$f.content}'
               data-fancybox-group="thumb">
              <img src='{$f.url_thumb}'
                   alt='{$f.file}'
                   title=''
                   class='js-image' />
            </a>
            {if $_SESSION.user.role >= 3}
              <p class='center'>
                <a href='{$f.url_update}'>
                  <span class='icon-pencil js-tooltip'
                        title='{$lang.global.update.update}'></span>
                </a>
                <a href='#' onclick="confirmDestroy('{$f.url_destroy}')">
                  <span class='icon-trash js-tooltip'
                        title='{$lang.global.destroy.destroy}'></span>
                </a>
              </p>
            {/if}
          </li>
        {/foreach}
      </ul>
      {if $_SESSION.user.role >= 3}
        <div class='form-actions' style='display:none'>
          <input id='js-update-order' type='button'
                class='btn btn-primary'
                value='{$lang.galleries.files.update.order}' />
        </div>
        <script src='{$_PATH.js}/core/jquery.ui.1.8.20.custom{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
        <script type='text/javascript'>
          $(document).ready(function(){
            $('.thumbnails').sortable({
              update : function () {
                $('#js-update-order').parent().fadeIn();
              }
            });

            $('#js-update-order').click(function() {
              $(this).val(lang.loading).attr('disabled', 'disabled');
              var order = $('.thumbnails').sortable('serialize');
              $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/updatefilepositions', order, function(data) {
                if(data == true) {
                  $('#js-update-order').parent().fadeOut(function() {
                    $('#js-update-order').val('{$lang.galleries.files.update.order}').removeAttr('disabled');
                  });
                }
                else {
                  $('#js-update-order').val('{$lang.galleries.files.update.order}').removeAttr('disabled');
                }
              }, 'json');
            });
          });
        </script>
      {/if}
      <p class='center'>
        <a href='/rss/{$_REQUEST.controller}/{$_REQUEST.id}'
           class='js-tooltip'
           title='{$lang.global.rss}'>
          <span class='icon-rss js-tooltip'
                title='{$lang.global.rss}'></span>
        </a>
      </p>
    {/if}
  {/if}
  <script src='{$_PATH.js}/core/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script src='{$_PATH.js}/plugins/jquery.fancybox-thumbs{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
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
{/strip}