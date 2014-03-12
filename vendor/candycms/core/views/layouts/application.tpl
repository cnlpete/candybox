<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'
      xmlns:og='http://opengraphprotocol.org/schema/'
      xmlns:fb='http://www.facebook.com/2008/fbml'>
  <head>
    {* Production mode: Use compiled CSS *}
    <link href='{$_PATH.css}/application{$_SYSTEM.compress_files_suffix}.css'
          rel='stylesheet' type='text/css' media='screen, projection'/>

    <meta http-equiv='content-type' content='text/html;charset=utf-8'/>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0'/>
    <meta name='description' content="{$_WEBSITE.meta.description}"/>
    <meta name='dc.title' content="{$_WEBSITE.title}"/>
    <meta itemprop='name' content="{$_WEBSITE.meta.og.title}"/>
    <meta itemprop='description' content="{$_WEBSITE.meta.og.description}"/>
    {if $_WEBSITE.meta.keywords}
      <meta name='keywords' content="{$_WEBSITE.meta.keywords}"/>
    {/if}

    {* Provide more details for specific entry. *}
    {if isset($_REQUEST.id)}
      <meta property='og:description' content="{$_WEBSITE.meta.og.description}"/>
      <meta property='og:site_name' content="{$_WEBSITE.meta.og.site_name}"/>
      <meta property='og:title' content="{$_WEBSITE.meta.og.title}"/>
      <meta property='og:url' content="{$_WEBSITE.meta.og.url}"/>
    {/if}

    {* Include additional meta data from PluginManager *}
    <!-- pluginmanager:sessionplugin::meta -->

    {* Basic stuff *}
    {foreach $_WEBSITE.meta.rss as $rss}
      <link href='{$rss.url}' rel='alternate' type='application/rss+xml' title='{$rss.title}'/>
    {/foreach}
    <link href='{$_PATH.public}/favicon.ico' rel='shortcut icon' type='image/x-icon'/>

    {* Include jQuery and its components *}
    <script type='text/javascript' src='http://code.jquery.com/jquery-2.0.3{$_SYSTEM.compress_files_suffix}.js'></script>

    {* Fallback if CDN is not available. Also include language parts. *}
    <script type='text/javascript'>
      var lang = {$_SYSTEM.json_language};

      if (typeof jQuery === 'undefined')
        document.write(unescape("%3Cscript src='{$_PATH.js.core}/jquery{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'%3E%3C/script%3E"));
    </script>

    <title>{$_WEBSITE.title} - {$WEBSITE_NAME}</title>
  </head>
  <!--[if lt IE 7]><body class='ie6'><![endif]-->
  <!--[if IE 7]><body class='ie7'><![endif]-->
  <!--[if IE 8]><body class='ie8'><![endif]-->
  <!--[if gt IE 8]><!--><body itemscope itemtype='http://schema.org/WebPage'><!--<![endif]-->

    {* Top navigation *}
    <nav class='navbar navbar-inverse navbar-fixed-top'>
      <div class='container'>
        <div class='navbar-header'>
          <a href='/' class='navbar-brand' title='{$WEBSITE_NAME}'>
            {$WEBSITE_NAME}
            {if $WEBSITE_MODE !== 'production'}
              - {$WEBSITE_MODE|upper}
            {/if}
          </a>
        </div>
        <div class='navbar-collapse collapse'>
          <ul class='nav navbar-nav'>
            <li{if $_REQUEST.controller == 'blogs'} class='active'{/if}>
              <a href='/blogs'>{$lang.global.blog}</a>
            </li>
            <li{if $_REQUEST.controller == 'galleries'} class='active'{/if}>
              <a href='/galleries'>{$lang.global.gallery}</a>
            </li>
            <li{if $_REQUEST.controller == 'calendars'} class='active'{/if}>
              <a href='/calendars'>{$lang.global.calendar}</a>
            </li>
            <li{if $_REQUEST.controller == 'downloads'} class='active'{/if}>
              <a href='/downloads'>{$lang.global.download}</a>
            </li>
            <li{if $_REQUEST.controller == 'searches'} class='active'{/if}>
              <a href='/searches'>{$lang.global.search}</a>
            </li>
          </ul>
          <ul class='nav navbar-nav navbar-right'>
            {if $_SESSION.user.role == 0}
              <li{if $_REQUEST.controller == 'users' && isset($_REQUEST.action) && $_REQUEST.action == 'create'} class='active'{/if}>
                <a href='/users/create'>
                  {$lang.global.register}
                </a>
              </li>
              <li class='divider-vertical'/>
              <li{if $_REQUEST.controller == 'sessions'} class='active'{/if}>
                <a href='/sessions/create'>
                  {$lang.global.login}
                </a>
              </li>
            {else}
              <li class='dropdown'>
                <a href='#' class='dropdown-toggle' data-toggle='dropdown'>
                  <strong>{$lang.global.welcome} {$_SESSION.user.name}!</strong>
                  <b class='caret'></b>
                </a>
                <ul class='dropdown-menu'>
                  {if $_SESSION.user.id > 0}
                    <li>
                      <a href='/users/{$_SESSION.user.id}/update'>
                        {$lang.global.settings}
                      </a>
                    </li>
                  {/if}
                  <li>
                    <a href='/sessions/destroy'>{$lang.global.logout}</a>
                  </li>
                  {if $_SESSION.user.role >= 3}
                    <li class='divider'></li>
                    <li>
                      <a href='/medias' title='{$lang.global.manager.media}'>
                        {$lang.global.manager.media}
                      </a>
                    </li>
                    <li>
                      <a href='/contents' title='{$lang.global.manager.content}'>
                        {$lang.global.manager.content}
                      </a>
                    </li>
                    {if $_SESSION.user.role == 4}
                      <li>
                        <a href='/logs' title='{$lang.global.logs}'>
                          {$lang.global.logs}
                        </a>
                      </li>
                      <li>
                        <a href='/users' title='{$lang.global.manager.user}'>
                          {$lang.global.manager.user}
                        </a>
                      </li>
                      <li>
                        <a href='/mails' title='{$lang.global.mails}'>
                          {$lang.global.mails}
                        </a>
                      </li>
                      <li>
                        <a href='/admins' title='{$lang.global.admins}'>
                          {$lang.global.admins}
                        </a>
                      </li>
                    {/if}
                  {/if}
                </ul>
              </li>
            {/if}
          </ul>
        </div>
      </div>
    </nav>

    {* Main container *}
    <div class='container'>
      <div class='row'>
        <div class='col-md-8'>
          {nocache}
            {if $_FLASH.message}
              <div id='js-flash_message'>
                <div id='js-flash_{$_FLASH.type}' class='alert alert-{$_FLASH.type} alert-dismissable'>
                  <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                  <strong>{$_FLASH.headline}</strong> {$_FLASH.message}
                </div>
              </div>
            {/if}
            {if isset($_WEBSITE.update) && !empty($_WEBSITE.update)}
              <div class='alert alert-warning alert-dismissable''>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                {$_WEBSITE.update}
              </div>
            {/if}
          {/nocache}
          <section id='{$_REQUEST.controller}' class='{$_REQUEST.action}' itemprop='mainContentOfPage'>
            {$_WEBSITE.content}
          </section>
        </div>
        <div class='col-md-4'>
          <!-- plugin:languagechooser -->
          <h3>{$lang.global.headlines}</h3>
          <!-- plugin:headlines -->
          <h3>{$lang.global.archive}</h3>
          <!-- plugin:archive -->
          <h3>{$lang.global.tagcloud}</h3>
          <!-- plugin:tagcloud -->
          <p>
            <a href='https://github.com/marcoraddatz/candyCMS/issues'
               target='_blank'
               class='btn btn-danger'>
              Report Bug
            </a>
          </p>
          {if $MOBILE_DEVICE}
            <p>
              <a href='/?mobile=1' ref='nofollow'>{$lang.global.view.mobile}</a>
            </p>
          {/if}
        </div>
      </div>
    </div>

    {* Add bootstrap support *}
    <script type='text/javascript' src='{$_PATH.js.bootstrap}/dropdown{$_SYSTEM.compress_files_suffix}.js'></script>
    <script type='text/javascript' src='{$_PATH.js.bootstrap}/tooltip{$_SYSTEM.compress_files_suffix}.js'></script>

    {* Own JS and plugins *}
    <script type='text/javascript' src='{$_PATH.js.core}/scripts{$_SYSTEM.compress_files_suffix}.js'></script>
    <!-- pluginmanager:sessionplugin::javascript -->
    <!-- plugin:analytics -->
    <!-- plugin:piwik -->
    <!-- plugin:formattimestamp -->
  </body>
</html>