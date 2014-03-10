<div class='page-header'>
  <h1>
    {if $_REQUEST.action == 'create'}
      {$lang.blogs.title.create}
    {else}
      {$lang.blogs.title.update|replace:'%s':$title}
    {/if}
  </h1>
</div>
<form method='post' class='form-horizontal'>
  <div class='control-group{if isset($error.title)} alert alert-error{/if}'>
    <label for='input-title' class='control-label'>
      {$lang.global.title} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <input name='{$_REQUEST.controller}[title]'
             value="{$title}"
             type='text'
             id='input-title'
             class='span4 required'
             maxlength='128'
             required />
      <span class='help-inline'>
        {if isset($error.title)}
          {$error.title}
        {/if}
      </span>
    </div>
  </div>
  <div class='control-group'>
    <label for='input-priority' class='control-label'>
      {$lang.global.priority}
    </label>
    <div class='controls'>
      <input name='{$_REQUEST.controller}[sticky]'
             value='1'
             type='checkbox'
             class='checkbox'
             id='input-sticky'
             {if $sticky == true}checked{/if} />
    </div>
  </div>
  <div class='control-group'>
    <label for='input-teaser' class='control-label'>
      {$lang.global.teaser}
    </label>
    <div class='controls'>
      <input name='{$_REQUEST.controller}[teaser]'
             value="{$teaser}"
             type='text'
             class='span4'
             maxlength='180'
             id='input-teaser' />
      <span class='help-inline'></span>
      <p class='help-block'>
        {$lang.blogs.info.teaser}
      </p>
    </div>
  </div>
  <div class='control-group'>
    <label for='input-tags' class='control-label'>
      {$lang.global.tags.tags}
    </label>
    <div class='controls'>
      <input type='text'
             name='{$_REQUEST.controller}[tags]'
             id='input-tags'
             data-provide='typeahead'
             value="{$tags}"
             data-source='{$_tags_}'
             data-items='8'
             autocomplete='off'
             class='span4' />
      <p class='help-block'>
        {$lang.blogs.info.tag}
      </p>
    </div>
  </div>
  <div class='control-group'>
    <label for='input-keywords' class='control-label'>
      {$lang.global.keywords}
    </label>
    <div class='controls'>
      <input name='{$_REQUEST.controller}[keywords]'
             value="{$keywords}"
             type='text'
             id='input-keywords'
             title='{$lang.blogs.info.keywords}'
             class='span4' />
      <p class='help-block'>
        {$lang.blogs.info.keywords}
      </p>
    </div>
  </div>

  <div class='control-group{if isset($error.content)} alert alert-error{/if}'>
    <label for='input-content' class='control-label'>
      {$lang.global.content} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <textarea name='{$_REQUEST.controller}[content]'
                class='js-editor required span5'
                id='input-content'
                rows='10'>{$content}</textarea>
      {if isset($error.content)}
        <span class='help-inline'>{$error.content}</span>
      {elseif count($editorinfo) > 0}
        <span class='help-block'>
          {$lang.global.editorinfo}
          {foreach $editorinfo as $aMarkup}
            <a href='{$aMarkup.url}' title='{$aMarkup.description}' class='js-tooltip'>
              <img src='{$aMarkup.iconurl}' />
            </a>
          {/foreach}
        </span>
      {/if}
    </div>
  </div>
  <div class='control-group'>
    <label for='input-language'
           class='control-label'>
      {$lang.global.language}
    </label>
    <div class='controls'>
      <select name='{$_REQUEST.controller}[language]'
              class='span4'
              id='input-language'>
        <option value=''>{$lang.global.multilingual}</option>
        {foreach $languages as $l}
          {if $_REQUEST.action == 'create'}
            <option value='{$l}' {if $l == $WEBSITE_LANGUAGE}selected='selected'{/if}>{$l}</option>
          {else}
            <option value='{$l}' {if $l == $language}selected='selected'{/if}>{$l}</option>
          {/if}
        {/foreach}
      </select>
    </div>
  </div>
  <div class='control-group'>
    <label for='input-published' class='control-label'>
      {$lang.global.published}
    </label>
    <div class='controls'>
      <input name='{$_REQUEST.controller}[published]'
             value='1'
             type='checkbox'
             class='checkbox'
             id='input-published'
             {if $published == true}checked{/if} />
    </div>
  </div>
  {if $_REQUEST.action == 'update'}
    <div class='control-group'>
      <label for='input-update_date' class='control-label'>
        {$lang.blogs.label.date}
      </label>
      <div class='controls'>
        <input name='{$_REQUEST.controller}[update_date]'
                value='1'
                type='checkbox'
                id='input-update_date'
                class='checkbox' />
      </div>
    </div>
    <div class='control-group'>
      <label for='input-show_update' class='control-label'>
        {$lang.global.update.show}
      </label>
      <div class='controls'>
        <input type='checkbox'
                class='checkbox'
                name='{$_REQUEST.controller}[show_update]'
                value='1'
                id='input-show_update'
                {if $date_modified > 0}checked{/if} />
      </div>
    </div>
  {/if}
  <div class='form-actions'>
    {if isset($author_id)}
      <input type='hidden'
              value='{$author_id}'
              name='{$_REQUEST.controller}[author_id]' />
    {/if}
    {if $_REQUEST.action == 'create'}
      <input type='submit'
              class='btn btn-primary'
              value="{$lang.global.create.create}"
              data-theme='b' />
    {elseif $_REQUEST.action == 'update'}
      <input type='submit'
              class='btn btn-primary'
              value="{$lang.global.update.update}"
              data-theme='b' />
      <input type='button'
              class='btn btn-danger'
              value='{$lang.blogs.title.destroy}'
              onclick="confirmDestroy('/{$_REQUEST.controller}/{$_REQUEST.id}/destroy')" />
      <input type='reset'
              class='btn'
              value='{$lang.global.reset}' />
      <input type='hidden'
              value='{$date}'
              name='{$_REQUEST.controller}[date]' />
      <input type='hidden'
              name='method'
              value='PUT' />
    {/if}
  </div>
</form>
<script type='text/javascript' src='{$_PATH.js.bootstrap}/bootstrap-typeahead{$_SYSTEM.compress_files_suffix}.js'></script>
{if !$MOBILE}
  <!-- pluginmanager:editor -->
{/if}
