{strip}
  {if $PLUGIN_PIWIK_URL !== '' && $PLUGIN_PIWIK_ID !== '' &&
          ($WEBSITE_MODE == 'production' || $WEBSITE_MODE == 'staging')}
    <script type='text/javascript'>
      var siteId  = '{$PLUGIN_PIWIK_ID}';
      var siteURL = '{$PLUGIN_PIWIK_URL}';
      {literal}
        var _paq = _paq || [];
        (function(){ var u=(("https:" == document.location.protocol) ? "https://"+siteURL+"/" : "http://"+siteURL+"/");
          _paq.push(['setSiteId', siteId]);
          _paq.push(['setTrackerUrl', u+'piwik.php']);
          _paq.push(['trackPageView']);
          _paq.push(['enableLinkTracking']);
          var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.defer=true; g.async=true; g.src=u+'piwik.js';
          s.parentNode.insertBefore(g,s);
        })();
      {/literal}
    </script>
  {else}
    <script type='text/javascript'>
      console.log('Piwik code would be shown in production and staging mode.');
    </script>
  {/if}
{/strip}