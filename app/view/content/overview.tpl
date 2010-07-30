{if $UR > 3}
  <p>
    <a href='/Content/create'>
      <img src='%PATH_IMAGES%/spacer.gif' class="icon-create" alt='' />
      &nbsp;
      {$lang_create_entry_headline}
    </a>
  </p>
{/if}
<table>
  <tr>
  <th colspan='4'>{$lang_headline}</th>
  </tr>
  {foreach from=$content item=c}
    <tr style='background:{cycle values="transparent,#eee"}'>
      <td style='width:45%' class="left">
        <a href='/Content/{$c.id}/{$c.eTitle}'>
        {$c.title}
        </a>
      </td>
      <td style='width:25%'>{$c.date}</td>
      <td style='width:20%'>
        <a href='/User/{$c.authorID}'>
          {$c.name} {$c.surname}
        </a>
      </td>
      <td style='width:10%'>
        {if $UR > 3}
          <a href='/Content/update/{$c.id}'>
            <img src='%PATH_IMAGES%/spacer.gif' class="icon-update" alt='{$lang_update}'
              title='{$lang_update}' />
          </a>
          &nbsp;
          <img src='%PATH_IMAGES%/spacer.gif' class="icon-destroy pointer" alt='{$lang_destroy}'
            title='{$lang_destroy}'
            onclick="confirmDelete('{$c.title}', '/Content/destroy/{$c.id}')" />
        {/if}
      </td>
    </tr>
  {/foreach}
</table>