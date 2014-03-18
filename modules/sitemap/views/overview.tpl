<div class='page-header'>
  <h1>{$lang.global.sitemap}</h1>
</div>
<div class='tabbable'>
  <ul class='nav nav-tabs'>
    {if $blogs}
      <li class='active'>
        <a href='#sitemap-blogs' data-toggle='tab'>
          {$lang.global.blogs}
        </a>
      </li>
    {/if}
    {if $contents}
      <li>
        <a href='#sitemap-contents' data-toggle='tab'>
          {$lang.global.contents}
        </a>
      </li>
    {/if}
    {if $galleries}
      <li>
        <a href='#sitemap-galleries' data-toggle='tab'>
          {$lang.global.galleries}
        </a>
      </li>
    {/if}
  </ul>
  <div class='tab-content'>
    <div class='tab-pane active' id='sitemap-blogs'>
      <ol>
        {foreach $blogs as $b}
          <li>
            <a href='{$b.url}'>{$b.title}</a>
          </li>
        {/foreach}
      </ol>
    </div>
    <div class='tab-pane' id='sitemap-contents'>
      <ol>
        {foreach $contents as $c}
          <li>
            <a href='{$c.url}'>{$c.title}</a>
          </li>
        {/foreach}
      </ol>
    </div>
    <div class='tab-pane' id='sitemap-galleries'>
      <ol>
        {foreach $galleries as $g}
          <li>
            <a href='{$g.url}'>{$g.title}</a>
          </li>
        {/foreach}
      </ol>
    </div>
  </div>
</div>
<script type='text/javascript' src='{$_PATH.js.bootstrap}/bootstrap-tab{$_SYSTEM.compress_files_suffix}.js'></script>