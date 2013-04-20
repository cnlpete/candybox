{strip}
<article class='gallery_albums' itemscope itemtype='http://schema.org/ImageGallery'>
  <header>
    <h2 itemprop='headline'>
      <a href='{$album.url}'>{$album.title}</a>
    </h2>
    <p>
      <time datetime='{$album.date.w3c}' class='js-timeago' itemprop='dateCreated'>
        {$album.date.raw|date_format:$lang.global.time.format.datetime}
      </time> - <span itemprop='userInteraction'>{$album.files_sum} {$lang.global.files}</span>
    </p>
  </header>
  {if $album.files_sum > 0}
    <ul class='thumbnails'>
      {foreach $album.files as $f}
        <li>
          <a href='{$album.url}' class='thumbnail'>
            <img src='{$f.url_32}' alt='{$f.file}' height='32' width='32' />
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
{/strip}
