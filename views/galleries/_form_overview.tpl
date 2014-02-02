<form method='post' class='form-horizontal'>
  <div class='page-header'>
    <h1>
      {if $_REQUEST.action == 'create'}
        {$lang.galleries.albums.title.create}
      {else}
        {$lang.galleries.albums.title.update|replace:'%s':$title}
      {/if}
    </h1>
  </div>
  <div class='control-group{if isset($error.title)} alert alert-error{/if}'>
    <label for='input-title' class='control-label'>
      {$lang.global.title} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <input name='{$_REQUEST.controller}[title]'
             value="{$title}"
             id='input-title'
             class='required span4 focused'
             type='text'
             maxlength='50'
             autofocus required />
      <span class='help-inline'>
        {if isset($error.title)}
          {$error.title}
        {/if}
      </span>
    </div>
  </div>
  <div class='control-group'>
    <label for='input-content' class='control-label'>
      {$lang.global.description}
    </label>
    <div class='controls'>
      <input name='{$_REQUEST.controller}[content]'
             value="{$content}"
             id='input-content'
             type='text'
             maxlength='160'
             class='span4' />
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
  <div class='form-actions'>
    {if $_REQUEST.action == 'create'}
      <input type='submit'
             class='btn btn-primary'
             value='{$lang.global.create.create}' />
    {elseif $_REQUEST.action == 'update'}
      <input type='submit'
             class='btn btn-primary'
             value='{$lang.global.update.update}' />
      <input type='button'
             value='{$lang.galleries.albums.title.destroy}'
             class='btn btn-danger'
             onclick="confirmDestroy('/{$_REQUEST.controller}/{$_REQUEST.id}/destroy')" />
      <input type='reset'
             value='{$lang.global.reset}'
             class='btn' />
      <input type='hidden'
             name='method'
             value='PUT' />
    {/if}
  </div>
</form>
{* @todo add countCharLength again at file upload *}