<ul>
  {foreach $files as $file}
    <li>
      <a class='js-tooltip js-migration' href='#' title='{$file.query}' data-file='{$file.name}'>
        {$file.name}
      </a>
    </li>
  {/foreach}
</ul>
<script type='text/javascript' src='../public/js/core/jquery.bootstrap.tooltip.js'></script>
<script type='text/javascript' src='../public/js/core/scripts.js'></script>
<script type='text/javascript'>
  $('.js-migration').click(function () {
    jTarget = $(this).parent();
    $.getJSON('?file=' + $(this).data('file') + '&action=migrate', function (data) {
      console.log(data);
      if (data) {
        jTarget.addClass('alert alert-success');
        jTarget.fadeOut();
      }
      else {
        jTarget.addClass('alert alert-error');
      }
    });
    return false;
  });
</script>