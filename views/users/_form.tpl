{strip}
  <div class='page-header'>
    <h1>{$lang.users.title.update}</h1>
  </div>
  <div class='tabbable'>
    <ul class='nav nav-tabs'>
      <li{if $_REQUEST['action'] == 'update'} class='active'{/if}>
        <a href='#user-personal' data-toggle='tab'>
          {$lang.users.title.personal_data}
        </a>
      </li>
      {if $_SESSION.user.id == $uid}
        <li{if $_REQUEST['action'] == 'password'} class='active'{/if}>
          <a href='#user-password' data-toggle='tab'>
            {$lang.users.title.password}
          </a>
        </li>
      {/if}
      <li id='js-avatar_tab'
          {if $_REQUEST['action'] == 'avatar' && $use_gravatar == 1}class='active hide'
          {elseif $use_gravatar == 1}class='hide'
          {elseif $_REQUEST['action'] == 'avatar'}class='active'{/if}>
        <a href='#user-image' data-toggle='tab'>
          {$lang.users.title.image}
        </a>
      </li>
      {if $_SESSION.user.role < 4}
        <li{if $_REQUEST['action'] == 'destroy'} class='active'{/if}>
          <a href='#user-destroy' data-toggle='tab'>
            {$lang.users.title.account}
          </a>
        </li>
      {/if}
      <li class='pull-right'>
        <a href='/users/{$_REQUEST.id}'>
          {$lang.users.title.profile}
        </a>
      </li>
    </ul>
  </div>
  <div class='tab-content'>

    {* Account data *}
    <div class="tab-pane{if $_REQUEST['action'] == 'update'} active{/if}" id='user-personal'>
      <form method='post' action='/{$_REQUEST.controller}/{$uid}/update' class='form-horizontal'>
        <div class='control-group{if isset($error.name)} alert alert-error{/if}'>
          <label for='input-name' class='control-label'>
            {$lang.global.name} <span title='{$lang.global.required}'>*</span>
          </label>
          <div class='controls'>
            <input class='span4 required'
                    name='{$_REQUEST.controller}[name]'
                    value="{$name}"
                    type='text'
                    id='input-name'
                    required />
            {if isset($error.name)}
              <span class='help-inline'>
                {$error.name}
              </span>
            {/if}
          </div>
        </div>
        <div class='control-group'>
          <label for='input-surname' class='control-label'>
            {$lang.global.surname}
          </label>
          <div class='controls'>
            <input class='span4'
                    name='{$_REQUEST.controller}[surname]'
                    value="{$surname}"
                    type='text'
                    id='input-surname' />
          </div>
        </div>
        <div class='control-group'>
          <label class='control-label'>
            {$lang.global.api_token}
          </label>
          <div class='controls'>
            <span class='uneditable-input span4'>
              {$api_token}
            </span>
          </div>
        </div>
        <div class='control-group'>
          <label for='input-use_gravatar' class='control-label'>
            {$lang.users.label.gravatar}
          </label>
          <div class='controls'>
            <input type='checkbox'
                    class='checkbox'
                    name='{$_REQUEST.controller}[use_gravatar]'
                    id='input-use_gravatar'
                    {if $use_gravatar == 1}checked{/if} />
            <div class='help-inline'>
              <a href='{$gravatar_avatar_popup}'
                  class='thumbnail js-fancybox'
                  title='{$full_name}'
                  id='js-gravatar'
                  style='{if $use_gravatar == 0}opacity:0.25{/if}'>
                <img alt='{$name} {$surname}'
                      src='{$gravatar_avatar_32}'
                      width='32' height='32' />
              </a>
            </div>
            <p id='js-gravatar_help'
                class='help-block{if $use_gravatar == 1} hide{/if}'>
              {$lang.users.info.gravatar}
            </p>
          </div>
        </div>
        <div class='control-group'>
          <label for='input-content' class='control-label'>
            {$lang.users.label.content.update}
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
            <textarea name='{$_REQUEST.controller}[content]'
                      rows='6'
                      class='span4'
                      maxlength='1000'
                      id='input-content'>
              {$content}
            </textarea>
            <span class='help-inline'></span>
          </div>
        </div>
        <div class='control-group'>
          <label for='input-receive_newsletter' class='control-label'>
            {$lang.users.label.newsletter}
          </label>
          <div class='controls'>
            <input name='{$_REQUEST.controller}[receive_newsletter]'
                    id='input-receive_newsletter'
                    value='1'
                    type='checkbox'
                    class='checkbox' {if $receive_newsletter == 1}checked{/if} />
          </div>
        </div>
        {if $_SESSION.user.role == 4 && $_SESSION.user.id !== $uid}
          <div class='control-group'>
            <label for='input-role' class='control-label'>
              {$lang.global.user.role}
            </label>
            <div class='controls'>
              <select name='{$_REQUEST.controller}[role]' id='input-role' class='span4'>
                <option value='1'{if $role == 1} selected{/if}>{$lang.global.user.roles.1}</option>
                <option value='2'{if $role == 2} selected{/if}>{$lang.global.user.roles.2}</option>
                <option value='3'{if $role == 3} selected{/if}>{$lang.global.user.roles.3}</option>
                <option value='4'{if $role == 4} selected{/if}>{$lang.global.user.roles.4}</option>
              </select>
            </div>
          </div>
        {/if}
        <div class='form-actions'>
          <input type='submit'
                  class='btn btn-primary'
                  value='{$lang.users.label.update}' />
          <input type='reset'
                  class='btn'
                  value='{$lang.global.reset}' />
          <input type='hidden'
                  value="{$email}"
                  name='{$_REQUEST.controller}[email]' />
          <input type='hidden'
                name='method'
                value='PUT' />
        </div>
      </form>
    </div>

    {* Password *}
    {if $_SESSION.user.id == $uid}
      <div class="tab-pane{if $_REQUEST['action'] == 'password'} active{/if}" id='user-password'>
        <form method='post'
              action='/{$_REQUEST.controller}/{$uid}/password'
              class='form-horizontal'>
          <div class='control-group{if isset($error.password_old)} alert alert-error{/if}'>
            <label for='input-password_old' class='control-label'>
              {$lang.users.label.password.old} <span title='{$lang.global.required}'>*</span>
            </label>
            <div class='controls'>
              <input name='{$_REQUEST.controller}[password_old]'
                      id='input-password_old'
                      type='password'
                      class='span4 required'
                      required />
              {if isset($error.password_old)}
                <span class='help-inline'>
                  {$error.password_old}
                </span>
              {/if}
            </div>
          </div>
          <div class='control-group{if isset($error.password_new)} alert alert-error{/if}'>
            <label for='input-password_new' class='control-label'>
              {$lang.users.label.password.new} <span title='{$lang.global.required}'>*</span>
            </label>
            <div class='controls'>
              <input name='{$_REQUEST.controller}[password_new]'
                      id='input-password_new'
                      type='password'
                      class='span4 required'
                      required />
              {if isset($error.password_new)}
                <span class='help-inline'>
                  {$error.password_new}
                </span>
              {/if}
            </div>
          </div>
          <div class='control-group'>
            <label for='input-password_new2' class='control-label'>
              {$lang.global.password.repeat} <span title='{$lang.global.required}'>*</span>
            </label>
            <div class='controls'>
              <input name='{$_REQUEST.controller}[password_new2]'
                      id='input-password_new2'
                      type='password'
                      class='span4 required'
                      required />
              {if isset($error.password_new2)}
                <span class='help-inline'>
                  {$error.password_new2}
                </span>
              {/if}
            </div>
          </div>
          <div class='form-actions'>
            <input type='submit'
                    class='btn btn-primary'
                    value='{$lang.users.label.password.create}' />
            <input type='reset'
                    class='btn'
                    value='{$lang.global.reset}' />
            <input type='hidden'
                  name='method'
                  value='PUT' />
          </div>
        </form>
      </div>
    {/if}

    {* Avatar *}
    <div class="tab-pane{if $_REQUEST['action'] == 'avatar'} active{/if}" id='user-image'>
      <div class='form-horizontal'>
        <div class='control-group{if isset($error.image)} alert alert-error{/if}'>
          {if $standard_avatar_popup !== $gravatar_avatar_popup}
            <div class='pull-right'>
              <a href='{$standard_avatar_popup}'
                  id='js-avatar_link'
                  class='thumbnail js-fancybox'
                  title='{$full_name}'>
                <img alt='{$name} {$surname}'
                      src='{$standard_avatar_64}'
                      width='64'
                      height='64'
                      id='js-avatar_thumb'/>
              </a>
            </div>
          {/if}
          <label for='input-image' class='control-label'>
            {$lang.users.label.image.choose}
          </label>
          <div class='controls'>
            <input type='file'
                    name='image'
                    id='input-image'
                    class='span4'
                    accept='image/jpg,image/gif,image/png' />
            {if isset($error.image)}
              <span class='help-inline'>
                {$error.image}
              </span>
            {/if}
            <span class='help-block'>
              {if $_SYSTEM.maximumUploadSize.raw <= 1536}
                {$_SYSTEM.maximumUploadSize.b|string_format: $lang.users.info.image}
              {elseif $_SYSTEM.maximumUploadSize.raw <= 1572864}
                {$_SYSTEM.maximumUploadSize.kb|string_format: $lang.users.info.image}
              {else}
                {$_SYSTEM.maximumUploadSize.mb|string_format: $lang.users.info.image}
              {/if}
            </span>
          </div>
        </div>
        <div class='control-group'>
          <label for='input-terms' class='control-label'>
            {$lang.global.terms.terms} <span title='{$lang.global.required}'>*</span>
          </label>
          <div class='controls'>
            <label class='checkbox'>
              <input type='checkbox'
                      class='checkbox'
                      name='{$_REQUEST.controller}[terms]'
                      id='input-terms'
                      value='1' />
                {$lang.users.label.image.terms}
            </label>
          </div>
        </div>
        <div class='control-group hide' id='js-progress'>
          <label class='control-label'>
            {$lang.global.upload.status}
          </label>
          <div class='controls'>
            <div class='progress progress-success progress-striped active'
                role='progressbar'
                aria-valuemin='0'
                aria-valuemax='100'>
              <div id='js-progress_bar' class='bar'></div>
            </div>
          </div>
        </div>
        <div class='form-actions'>
          <input type='button'
                  id='js-submit_avatar'
                  class='btn btn-primary'
                  value='{$lang.users.title.image}' />
          <input type='reset'
                  class='btn'
                  value='{$lang.global.reset}' />
          <input type='hidden'
                  name='MAX_FILE_SIZE'
                  value='409600' />
        </div>
      </div>
    </div>

    {* Destroy account *}
    {if $_SESSION.user.role < 4}
      <div class="tab-pane{if $_REQUEST['action'] == 'destroy'} active{/if}" id='user-destroy'>
        <form method='post'
              action='/{$_REQUEST.controller}/{$uid}/destroy'
              class='form-horizontal'>
          <p class='alert alert-danger'>
            {$lang.users.info.destroy_account}
          </p>
          <div class='control-group'>
            <label for='input-password' class='control-label'>
              {$lang.global.password.password}
            </label>
            <div class='controls'>
              <input name='{$_REQUEST.controller}[password]'
                    type='password'
                    id='input-password'
                    class='span4' />
            </div>
          </div>
          <div class='form-actions'>
            <input type='submit'
                  class='btn btn-danger'
                  value='{$lang.users.label.account.destroy}' />
            <input type='hidden'
                  name='method'
                  value='DELETE' />
          </div>
        </form>
      </div>
    {/if}
  </div>
  <script src='{$_PATH.js.core}/jquery.fancybox{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script src='{$_PATH.js.bootstrap}/bootstrap-tab{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
  <script type='text/javascript'>
    $('#input-use_gravatar').change(function() {
      var avatarIsChecked = $(this).is(':checked');
      $('#js-gravatar').toggleOpacity(avatarIsChecked);
      $('#js-gravatar_help').toggle('fast');
      $('#js-avatar_tab').toggle(!avatarIsChecked);
    });

    $('#input-image').change(function() {
      checkFileSize($(this), {$_SYSTEM.maximumUploadSize.raw}, '{$_SYSTEM.maximumUploadSize.mb|string_format: $lang.error.file.size}');
      prepareForUpload();
    });

    $('#js-submit_avatar').click(function() {
      upload(this, '{$_REQUEST.controller}/{$uid}/avatar', '{$_REQUEST.controller}', 'image', 'terms', false);
    });

    $('.js-fancybox').fancybox();
  </script>
{/strip}
