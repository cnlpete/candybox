{if !$files}
  <div class='alert alert-danger'>
    <h4 class='alert-heading'>
      There are no required migrations!
    </h4>
  </div>
{else}
  <ul>
    {foreach $files as $file}
      <li>
        <a class='js-tooltip js-migration' href='#' title='{$file.query}' data-file='{$file.name}'>
          {$file.name}
        </a>
      </li>
    {/foreach}
  </ul>
{/if}
<div class='form-actions right'>
  {if $smarty.get.show && 'all' == $smarty.get.show}
    <a class='btn' href='/install/?action=migrate&show=version'>Show migrations for this version only</a>
  {else}
    <a class='btn' href='/install/?action=migrate&show=all'>Show older migrations</a>
  {/if}
</div>
<script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/jquery.bootstrap.tooltip.js'></script>
<script type='text/javascript' src='{$_PATH.core}/assets/javascripts/core/scripts.js'></script>
<script type='text/javascript'>
  $('.js-migration').click(function () {
    jTarget = $(this).parent();
    $.getJSON('?path={$smarty.get.show}&file=' + $(this).data('file') + '&action=migrate', function (data) {
      if (data) {
        jTarget.fadeOut();
      }
    });
    return false;
  });
</script>