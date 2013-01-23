{strip}
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='#'>
        <i class='icon-plus'
           title='{$lang.global.create.entry}'></i>
        {$lang.galleries.files.title.create}
      </a>
    </p>
  {/if}
  {if !$album.name}
    <div class='alert alert-warning'>
      <h4>{$lang.error.missing.entry}</h4>
    </div>
  {else}
    <header class='page-header'>
      <h1>
        {$album.name}
        <small>
          ({$file_no} {$lang.global.files})
        </small>
        {if $_SESSION.user.role >= 3}
          <a href='/{$_REQUEST.controller}/{$_REQUEST.id}/update'>
            <i class='icon-pencil js-tooltip'
               title='{$lang.global.update.update}'></i>
          </a>
        {/if}
      </h1>
    </header>
    {if $album.content}
      <p>{$album.content}</p>
    {/if}
    {if !$files}
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
               data-fancybox-group="thumb">
              <img src='{$f.url_thumb}'
                   alt='{$f.file}'
                   title=''
                   class='js-image' />
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
      {if $_SESSION.user.role >= 3}
        <div class='form-actions' style='display:none'>
          <input id='js-update-order' type='button'
                class='btn btn-primary'
                value='{$lang.galleries.files.update.order}' />
        </div>
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
              $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/updateorder', order, function(data) {
                if(data.success == true) {
                  $('#js-update-order').parent().fadeOut();
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
        <a href='{$album.url_clean}.rss'>
          <i class='icon-rss js-tooltip'
             title='{$lang.global.rss}'></i>
        </a>
      </p>
    {/if}
  {/if}
  {if $_SESSION.user.role >= 3}
    <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.ui{$_SYSTEM.compress_files_suffix}.js'></script>
  {else}
    <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.masonry{$_SYSTEM.compress_files_suffix}.js'></script>
    <script type='text/javascript'>
      $(document).ready(function(){
        $('.thumbnails').masonry({
          itemSelector: 'li'
        });
      });
    </script>
  {/if}
  <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js'></script>
  <script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.fancybox-thumbs{$_SYSTEM.compress_files_suffix}.js'></script>
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

      showAjaxUpload('js-gallery_upload', '{$_REQUEST.controller}', '{$_REQUEST.id}/createfile');
    });
  </script>
{/strip}