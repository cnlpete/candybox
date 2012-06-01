{strip}
  <div class='page-header'>
    <h1>
      {if $_REQUEST.action == 'create'}
        {$lang.contents.title.create}
      {else}
        {$lang.contents.title.update|replace:'%p':$title}
      {/if}
    </h1>
  </div>
  <form method='post' class='form-horizontal'
        action='/{$_REQUEST.controller}/{if isset($_REQUEST.id)}{$_REQUEST.id}/{/if}{$_REQUEST.action}'>
    <div class='control-group{if isset($error.title)} alert alert-error{/if}'>
      <label for='input-title' class='control-label'>
        {$lang.global.title} <span title='{$lang.global.required}'>*</span>
      </label>
      <div class='controls'>
        <input type='text' name='{$_REQUEST.controller}[title]' class='span4 required focused'
              value="{$title}" id='input-title' autofocus required />
        <span class='help-inline'>
          {if isset($error.title)}
            {$error.title}
          {/if}
        </span>
      </div>
    </div>
    <div class='control-group'>
      <label for='input-teaser' class='control-label'>
        {$lang.global.teaser}
      </label>
      <div class='controls'>
        <input name='{$_REQUEST.controller}[teaser]' value="{$teaser}" type='text' class='span4'
              id='input-teaser' />
        <span class='help-inline'></span>
        <p class='help-block'>
          {$lang.blogs.info.teaser}
        </p>
      </div>
    </div>
    <div class='control-group'>
      <label for='input-keywords' class='control-label'>
        {$lang.global.keywords}
      </label>
      <div class='controls'>
        <input name='{$_REQUEST.controller}[keywords]' value="{$keywords}" type='text'
              class='span4' id='input-keywords' />
        <p class='help-block'>
          {$lang.contents.info.keywords}
        </p>
      </div>
    </div>
    <div class='control-group{if isset($error.content)} alert alert-error{/if}'>
      <label for='input-content' class='control-label'>
        {$lang.global.content} <span title='{$lang.global.required}'>*</span>
      </label>
      <div class='controls'>
        <textarea name='{$_REQUEST.controller}[content]' class='js-tinymce required span4' id='input-content'>
          {$content}
        </textarea>
        {if isset($error.content)}
          <span class='help-inline'>
            {$error.content}
          </span>
        {/if}
      </div>
    </div>
    <div class='control-group'>
      <label for='input-published' class='control-label'>
        {$lang.global.published}
      </label>
      <div class='controls'>
        <input name='{$_REQUEST.controller}[published]' value='1' type='checkbox' class='checkbox'
              id='input-published' {if $published == true}checked{/if} />
      </div>
    </div>
    <div class='form-actions'>
      <input type='submit' class='btn btn-primary'
            value="{if $_REQUEST.action == 'create'}{$lang.global.create.create}{else}{$lang.global.update.update}{/if}" />
      {if $_REQUEST.action == 'update'}
        <input type='button' class='btn btn-danger' value='{$lang.contents.title.destroy}'
              onclick="confirmDestroy('/{$_REQUEST.controller}/{$_REQUEST.id}/destroy')" />
        <input type='reset' class='btn' value='{$lang.global.reset}' />
      {/if}
    </div>
  </form>
  {if !$MOBILE}
    <!-- plugin:tinymce -->
  {/if}
  <script type='text/javascript'>
    $('#input-title').bind('keyup', function() {
      countCharLength(this, 128);
    });

    $('#input-teaser').bind('keyup', function() {
      countCharLength(this, 180);
    });
  </script>
{/strip}
