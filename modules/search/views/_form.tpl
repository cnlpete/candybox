<form method='get' class='form-horizontal' role='form'>
  {if !$MOBILE}
    <div class='page-header'>
      <h1>{$lang.global.search}</h1>
    </div>
  {/if}

  <div class='form-group'>
    <label for='input-search' class='control-label col-md-3'>
      {$lang.search.label.terms} <span title='{$lang.global.required}'>*</span>
    </label>
    <div class='col-md-8'>
      <input type='search'
             class='form-control focused'
             name='{$_REQUEST.controller}[search]'
             id='input-search'
             autofocus required />
    </div>
  </div>

  <div class='form-group'>
    <div class='col-sm-offset-3 col-sm-9'>
      <input type='submit'
             name='submit'
             class='btn btn-primary'
             value='{$lang.global.search}' />
    </div>
  </div>
</form>
