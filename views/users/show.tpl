{strip}
  <div class='page-header'>
    <h1>
      {$users.full_name}
      {if $_SESSION.user.role == 4 || $users.id == $_SESSION.user.id}
        <a href='{$users.url_update}'>
          <i class='icon-pencil js-tooltip'
              title='{$lang.global.update.update}'></i>
        </a>
      {/if}
    </h1>
  </div>
  <table class='table unstyled'>
    <tr>
      <td>
        {$lang.users.label.registered_since}
      </td>
      <td>
        <time datetime='{$users.date.w3c}' class='js-timeago'>
          {$users.date.raw|date_format:$lang.global.time.format.date}
        </time>
      </td>
      <td rowspan='4'>

        {* List as a fix to fit width *}
        <ul class='thumbnails'>
          <li>
            <a href='{$users.avatar_popup}'
                class='thumbnail js-fancybox'
                title='{$users.full_name}'>
              <img alt='{$users.full_name}'
                    src='{$users.avatar_100}'
                    width='100' />
            </a>
          </li>
        </ul>
      </td>
    </tr>
    <tr>
      <td>
        {$lang.users.label.last_login}
      </td>
      <td>
        <time datetime='{$users.last_login.w3c}' class='js-timeago'>
          {$users.last_login.raw|date_format:$lang.global.time.format.date}
        </time>
      </td>
    </tr>
    <tr>
      <td>
        {$lang.users.label.content.show|replace:'%s':$users.name}
      </td>
      <td>
        {$users.content}
      </td>
    </tr>
    <tr>
      <td>
        {$lang.global.contact}
      </td>
      <td>
        <a href='/mails/{$_REQUEST.id}/create'>
          {$lang.users.contact_via_email|replace:'%s':$users.name}
        </a>
      </td>
    </tr>
  </table>
  <script src='{$_PATH.js}/core/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script type='text/javascript'>
    $(document).ready(function(){
      $('.js-fancybox').fancybox();
    });
  </script>
{/strip}