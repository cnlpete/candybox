<div itemscope itemtype='http://schema.org/SearchResultsPage'>
  <div class='page-header'>
    <h1 itemprop='headline'>
      {$lang.global.search}
    </h1>
  </div>

  {if $tables.blogs.entries > 0 || $tables.contents.entries > 0 || $tables.downloads.entries > 0 || $tables.gallery_albums.entries > 0}
    <div class='tabbable' itemprop='breadcrumb'>
      <ul class='nav nav-tabs'>
        {foreach $tables as $table}
          <li{if $table@first} class='active'{/if}>
            <a href='#search-{$table.title|lower}' data-toggle='tab'>{$table.title} ({$table.entries})</a>
          </li>
        {/foreach}
      </ul>
    </div>
    <div class='tab-content'>
      {foreach $tables as $table}
        <div class='tab-pane{if $table@first} active{/if}' id='search-{$table.title|lower}'>
          {if $table.entries == 0}
            <div class='alert alert-warning'>
              <h4>{$lang.error.missing.entries}</h4>
            </div>
          {else}
            <ol>
              {foreach $table as $data}
                {if $data.id > 0}
                  <li>
                    <a href='{$data.url_clean}/highlight/{$string}'
                       itemprop='name'>
                      {$data.title}
                    </a>,
                    <time datetime='{$data.date.w3c}'
                          class='js-timeago'
                          itemprop='dateCreated'>
                      {$data.date.raw|date_format:$lang.global.time.format.datetime}
                    </time>
                  </li>
                {/if}
              {/foreach}
            </ol>
          {/if}
        </div>
      {/foreach}
    </div>
  {else}
    <div class='alert alert-warning'>
      {$lang.searches.info.fail|replace:'%s':$string}
      <br />
      <a href='/{$_REQUEST.controller}' class='alert-link'>{$lang.searches.info.research}</a>
    </div>
  {/if}

  <script type='text/javascript' src='{$_PATH.js.bootstrap}/tab{$_SYSTEM.compress_files_suffix}.js'></script>
</div>