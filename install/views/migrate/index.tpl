{if !$files}
  <div class='alert alert-danger'>
    <h4 class='alert-heading'>
      Good job! There are no outstanding migrations for this version.
    </h4>
    <p>
      If you came here to upgrade from candyCMS version 3.0.5 click
      <a href='/install/?action=migrate&show=all'>here</a>. Migrations below 3.0.5
      are put into "install/migrations/archive" and must be installed by hand.
    </p>
    <p>
      To finish the migration you can delete the install folder now and
      <a href='/sessions/create' class='session-login'>login</a>.
    </p>
  </div>
{else}
  <p>
    Please upgrade from top to bottom!
  </p>
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
<div class='form-actions'>
  {if $smarty.get.show && 'all' == $smarty.get.show}
    <a class='btn pull-right' href='/install/?action=migrate&show=version'>Show migrations for version 4 only</a>
  {else}
    <a class='btn pull-right' href='/install/?action=migrate&show=all'>Show older migrations (upgrading from 3.0.5)</a>
  {/if}
</div>
<script type='text/javascript' src='{$_PATH.js.bootstrap}/bootstrap-tooltip.js'></script>
<script type='text/javascript' src='{$_PATH.js.core}/scripts.js'></script>
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