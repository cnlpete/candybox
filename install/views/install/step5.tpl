<form action='/install/?action=migrate&show=version' method='post'>
  {if $_result_ == 'success'}
    <div class='alert alert-success'>
      <h4 class='alert-heading'>
        Congratulations!
      </h4>
      <p>
        Your installation was successful. Please
        <a href="/install/?action=migrate&show=version">
          migrate the database to the latest version
        </a> and delete the install folder afterwards!
      </p>
    </div>
  {else}
    <div class='alert alert-danger'>
      <h4 class='alert-heading'>
        Ooops!
      </h4>
      <p>
        The admin account could not be created. Please restart the installation.
      </p>
    </div>
  {/if}
  <div class='form-actions'>
    <input type='submit' class='btn pull-right' value='Migrate now &rarr;' />
  </div>
</form>