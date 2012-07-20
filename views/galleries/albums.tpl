{strip}
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/create'>
        <i class='icon-plus'
           title='{$lang.galleries.albums.title.create}'></i>
        {$lang.galleries.albums.title.create}
      </a>
    </p>
  {/if}
  <div class='page-header'>
    <h1>{$lang.global.gallery}</h1>
  </div>
  {if !$albums}
    <div class='alert alert-warning'>
      <h4>{$lang.error.missing.entries}</h4>
    </div>
  {else}
    {foreach $albums as $a}
      <article class='gallery_albums'>
        <header>
          <h2>
            <a href='{$a.url}'>{$a.title}</a>
            {if $_SESSION.user.role >= 3}
              <a href='{$a.url_createfile}'>
                <i class='icon-plus js-tooltip'
                   title='{$lang.galleries.files.title.create}'></i>
              </a>
              <a href='{$a.url_update}'>
                <i class='icon-pencil js-tooltip'
                   title='{$lang.global.update.update}'></i>
              </a>
            {/if}
          </h2>
          <p>
            <time datetime='{$a.date.w3c}' class='js-timeago'>
              {$a.date.raw|date_format:$lang.global.time.format.datetime}
            </time> - {$a.files_sum} {$lang.global.files}
          </p>
        </header>
        {if $a.files_sum > 0}
          <ul class='thumbnails'>
            {foreach $a.files as $f}
              <li>
                <a href='{$a.url}' class='thumbnail'>
                  <img src='{$f.url_32}'
                       alt='{$f.file}'
                       height='32' width='32' />
                </a>
              </li>
            {/foreach}
          </ul>
        {else}
          <div class='alert alert-warning'>
            <h4 class='alert-heading'>
              {$lang.error.missing.files}
            </h4>
          </div>
        {/if}
      </article>
    {/foreach}
    {$_pages_}
  {/if}
{/strip}