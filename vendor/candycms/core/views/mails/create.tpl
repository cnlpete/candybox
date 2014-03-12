<div class='page-header'>
  <h1>{$lang.global.contact} {$user.name} {$user.surname}</h1>
</div>
<form method='post' class='form-horizontal'>
  <div class='control-group{if isset($error.email)} alert alert-error{/if}'>
    <label for='input-email' class='control-label'>
      {$lang.global.email.email} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <input id='input-email'
             class='required span4'
             name='{$_REQUEST.controller}[email]'
             value="{if isset($email)}{$email}{/if}"
             type='email'
             required />
      {if isset($error.email)}
        <span class='help-inline'>
          {$error.email}
        </span>
      {/if}
    </div>
  </div>
  <div class='control-group'>
    <label for='input-subject' class='control-label'>
      {$lang.global.subject}
    </label>
    <div class='controls'>
      <input id='input-subject'
             class='span4'
             name='{$_REQUEST.controller}[subject]'
             value="{if isset($subject)}{$subject}{/if}"
             type='text' />
    </div>
  </div>
  <div class='control-group{if isset($error.content)} alert alert-error{/if}'>
    <label for='input-content' class='control-label'>
      {$lang.global.content} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <textarea class='required span5'
                id='input-content'
                name='{$_REQUEST.controller}[content]'
                rows='6'
                required>
        {if isset($content)}{$content}{/if}
      </textarea>
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
  <!-- pluginmanager:captcha -->
  <div class='form-actions'>
    <input type='submit'
           class='btn btn-primary'
           value='{$lang.global.submit}' />
  </div>
</form>