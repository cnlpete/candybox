<form action='/{$_REQUEST.controller}'
      method='post'
      data-ajax='false'
      class='form-horizontal'
      role="form">
  {if !$MOBILE}
    <div class='page-header'>
      <h1>{$lang.global.login}</h1>
    </div>
  {/if}
  <div class='form-group{if isset($error.email)} alert alert-error{/if}'>
    <label for='input-email' class='col-sm-4 control-label'>
      {$lang.global.email.email} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='col-sm-6'>
      <input name='{$_REQUEST.controller}[email]'
             class='form-control focused required'
             type='email'
             value='{$email}'
             id='input-email'
             autofocus required />
      {if isset($error.email)}<span class='help-inline'>{$error.email}</span>{/if}
    </div>
  </div>

  <div class='form-group{if isset($error.password)} alert alert-error{/if}'>
    <label for='input-password' class='col-sm-4 control-label'>
      {$lang.global.password.password} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='col-sm-6'>
      <input class='form-control required'
             name='{$_REQUEST.controller}[password]'
             type='password'
             id='input-password' required />
      {if isset($error.password)}
        <span class='help-inline'>
          {$error.password}
        </span>
      {/if}
    </div>
  </div>

  <div class='form-group'>
    <div class='col-sm-offset-4 col-sm-8'>
      <input type='submit' value='{$lang.global.login}' data-theme='b' class='btn btn-primary'/>
    </div>
  </div>
</form>

<div class='center'>
  <a href='/{$_REQUEST.controller}/password' class='btn'>
    {$lang.sessions.password.title}
  </a>
  <a href='/{$_REQUEST.controller}/verification' class='btn'>
    {$lang.sessions.verification.title}
  </a>
</div>