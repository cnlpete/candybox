{strip}
  {foreach $user as $u}
    <div class='page-header'>
      <h1>
        {$u.full_name}
        {if $_SESSION.user.role == 4 || $u.id == $_SESSION.user.id}
          <a href='{$u.url_update}'>
            <img src='{$_PATH.images}/candy.global/spacer.png'
                class='icon-update js-tooltip'
                alt='{$lang.global.update.update}'
                title='{$lang.global.update.update}'
                width='16' height='16' />
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
          <time datetime='{$u.date.w3c}'>
            {$u.date.raw|date_format:$lang.global.time.format.date}
          </time>
        </td>
        <td rowspan='4'>

          {* List as a fix to fit width *}
          <ul class='thumbnails'>
            <li>
              <a href='{$u.avatar_popup}'
                 class='thumbnail js-fancybox'
                 title='{$u.full_name}'>
                <img alt='{$u.full_name}'
                     src='{$u.avatar_100}'
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
          <time datetime='{$u.last_login.w3c}' class='js-timeago'>
            {$u.last_login.raw|date_format:$lang.global.time.format.date}
          </time>
        </td>
      </tr>
      <tr>
        <td>
          {$lang.users.label.content.show|replace:'%s':$u.name}
        </td>
        <td>
          {$u.content}
        </td>
      </tr>
      <tr>
        <td>
          {$lang.global.contact}
        </td>
        <td>
          <a href='/mails/{$_REQUEST.id}/create'>
            {$lang.users.contact_via_email|replace:'%s':$u.name}
          </a>
        </td>
      </tr>
    </table>
  {/foreach}
  <script src='{$_PATH.js}/core/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script type='text/javascript'>
    $(document).ready(function(){
      $('.js-fancybox').fancybox();
    });
  </script>
{/strip}
