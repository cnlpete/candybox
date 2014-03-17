{strip}
  <h3>{$lang.languagechooser.title}</h3>
  <section id='languages'>
    <ul>
      {foreach from=$languages item=l}
        <li>
          <a href='?language={$l.lang}' {if $l.selected}class='selected'{/if}>
              {$l.title}
          </a>
        </li>
      {/foreach}
    </ul>
  </section>
{/strip}