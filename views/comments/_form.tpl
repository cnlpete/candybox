{strip}
  <a name='create'></a>
  <div class='page-header'>
    <h2>
      {$lang.comments.title.create}
    </h2>
    {if $_SESSION.user.role == 0 && $_SYSTEM.hasSessionPlugin}
      <p>
        <!-- pluginmanager:sessionplugin::button -->
      </p>
    {/if}
  </div>
  <form method='post'
        action='/comments/create'
        data-ajax='false'
        class='form-horizontal'>
    <div class='control-group{if isset($error.name)} alert alert-error{/if}'>
      <label for='input-name' class='control-label'>
        {$lang.global.name} <span title='{$lang.global.required}'>*</span>
      </label>
      <div class='controls'>
        {if $_SESSION.user.name}
          <input type='text'
                 name='comments[name]'
                 value="{$_SESSION.user.full_name}"
                 id='input-name'
                 class='disabled span4'
                 disabled />
        {else}
          <input type='text'
                 value="{if isset($name)}{$name}{/if}"
                 name='comments[name]'
                 id='input-name'
                 class='required span4'
                 required />
          {if isset($error.name)}
            <span class='help-inline'>
              {$error.name}
            </span>
          {/if}
        {/if}
      </div>
    </div>
    <div class='control-group{if isset($error.email)} alert alert-error{/if}'>
      <label for='input-email' class='control-label'>
        {$lang.global.email.email}
      </label>
      <div class='controls'>
        {if $_SESSION.user.email}
          <input type='text' id='input-email' class='disabled span4' name='comments[email]'
                value="{$_SESSION.user.email}" disabled />
        {else}
          <input type='email'
                 class='span4'
                 value="{if isset($email)}{$email}{/if}"
                 name='comments[email]'
                 id='input-email' />
          {if isset($error.email)}
            <span class='help-inline'>
              {$error.email}
            </span>
          {/if}
        {/if}
      </div>
    </div>
    <div class='control-group{if isset($error.content)} alert alert-error{/if}'>
      <label for='js-create_commment_text' class='control-label'>
        {$lang.global.content} <span title='{$lang.global.required}'>*</span>
        {if count($editorinfo) > 0}
          <br />{$lang.global.editorinfo}<br />
          {foreach $editorinfo as $oMarkup}
            <a href="{$oMarkup.url}" title="{$oMarkup.description}" class='js-tooltip'>
              <img src="{$oMarkup.iconurl}" />
            </a>
          {/foreach}
        {/if}
      </label>
      <div class='controls'>
        <textarea name='comments[content]'
                  id='js-create_commment_text'
                  rows='5'
                  class='required span4'
                  required>
          {if isset($content)}{$content}{/if}
        </textarea>
        {if isset($error.content)}
          <span class='help-inline'>
            {$error.content}
          </span>
        {/if}
      </div>
    </div>
    <!-- pluginmanager:captcha -->
    {if $MOBILE}
      <div data-role='fieldcontain' class='center'>
    {/if}
    <div class='form-actions' data-role='controlgroup' data-type='horizontal'>
      <input type='submit'
             value='{$lang.comments.title.create}'
             data-theme='b'
             class='btn btn-primary' />
      <input type='reset'
             value='{$lang.global.reset}'
             class='btn' />
      <input type='hidden'
             value='{$_REQUEST.id}'
             name='comments[parent_id]' />
      <input type='hidden'
             value='{$_REQUEST.controller}'
             name='comments[parent_controller]' />
    </div>
    {if $MOBILE}
      </div>
    {/if}
  </form>
{/strip}
