<form action='?action=install&step=4' method='post' class='form-horizontal'>
  <div class='control-group{if isset($error.name)} alert alert-error{/if}'>
    <label for='input-name' class='control-label'>
      {$lang.global.name} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <input class='focused span4'
              name='{$_REQUEST.controller}[name]'
              value='{if isset($name)}{$name}{/if}'
              type='text'
              id='input-name'
              autofocus required />
      {if isset($error.name)}
        <span class='help-inline'>
          {$error.name}
        </span>
      {/if}
    </div>
  </div>
  <div class='control-group{if isset($error.surname)} alert alert-error{/if}'>
    <label for='input-surname' class='control-label'>
      {$lang.global.surname} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <input class='span4'
              name='{$_REQUEST.controller}[surname]'
              value='{if isset($surname)}{$surname}{/if}'
              id='input-surname'
              type='text'  />
      {if isset($error.surname)}
        <span class='help-inline'>
          {$error.surname}
        </span>
      {/if}
    </div>
  </div>
  <div class='control-group{if isset($error.email)} alert alert-error{/if}'>
    <label for='input-email' class='control-label'>
      {$lang.global.email.email} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <input class='span4'
              name='{$_REQUEST.controller}[email]'
              value='{if isset($email)}{$email}{/if}'
              type='email'
              id='input-email'
              required />
      {if isset($error.email)}
        <span class='help-inline'>
          {$error.email}
        </span>
      {/if}
    </div>
  </div>
  <div class='control-group{if isset($error.password)} alert alert-error{/if}'>
    <label for='input-password' class='control-label'>
      {$lang.global.password.password} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <input class='span4'
              name='{$_REQUEST.controller}[password]'
              type='password'
              id='input-password'
              required />
      {if isset($error.password)}
        <span class='help-inline'>
          {$error.password}
        </span>
      {/if}
    </div>
  </div>
  <div class='control-group' id='js-password'>
    <label for='input-password2' class='control-label'>
      {$lang.global.password.repeat} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='controls'>
      <input class='span4'
              name='{$_REQUEST.controller}[password2]'
              type='password'
              id='input-password2'
              required />
    </div>
  </div>
  <div class='form-actions right'>
    <input type='submit' class='btn' value='Step 5: Install admin user &rarr;' />
  </div>
</form>
<script type="text/javascript">
  $(document).ready(function(){
    $('#input-password2').keyup(function(){
      if ($('#input-password').val() == $('#input-password2').val()) {
        $('#js-password').removeClass('error');
      } else {
        $('#js-password').addClass('error');
      }
    });
  });
</script>