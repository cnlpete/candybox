{strip}
  <div itemscope itemtype='http://schema.org/ProfilePage'>
    <div class='page-header'>
      <h1 itemprop='author'>
        {$user.full_name}
        {if $_SESSION.user.role == 4 || $user.id == $_SESSION.user.id}
          <a href='{$user.url_update}'>
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
          <time datetime='{$user.date.w3c}'
                class='js-timeago'
                itemprop='dateCreated'>
            {$user.date.raw|date_format:$lang.global.time.format.date}
          </time>
        </td>
        <td rowspan='4'>

          {* List as a fix to fit width *}
          <ul class='thumbnails'>
            <li>
              <a href='{$user.avatar_popup}'
                  class='thumbnail js-fancybox'
                  title='{$user.full_name}'>
                <img alt='{$user.full_name}'
                      src='{$user.avatar_100}'
                      width='100'
                      itemprop='thumbnailUrl' />
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
          <time datetime='{$user.last_login.w3c}'
                class='js-timeago'
                itemprop='dateModified'>
            {$user.last_login.raw|date_format:$lang.global.time.format.date}
          </time>
        </td>
      </tr>
      <tr>
        <td>
          {$lang.users.label.content.show|replace:'%s':$user.name}
        </td>
        <td itemprop='text'>
          {$user.content}
        </td>
      </tr>
      <tr>
        <td>
          {$lang.global.contact}
        </td>
        <td>
          <a href='/mails/{$_REQUEST.id}/create'>
            {$lang.users.contact_via_email|replace:'%s':$user.name}
          </a>
        </td>
      </tr>
    </table>
    <script src='{$_PATH.js.core}/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
    <script type='text/javascript'>
      $(document).ready(function(){
        $('.js-fancybox').fancybox();
      });
    </script>
  </div>
{/strip}
