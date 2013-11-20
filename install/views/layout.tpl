<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv='content-type' content='text/html;charset=utf-8' />
    <link href='/public/favicon.ico' rel='shortcut icon' type='image/x-icon' />
    <link href='/public/stylesheets/core.css' rel='stylesheet' type='text/css' media='screen, projection' />
    <script type='text/javascript' src='http://code.jquery.com/jquery-1.8.2{$_SYSTEM.compress_files_suffix}.js'></script>
    <script type="text/javascript">
      if (typeof jQuery == 'undefined')
        document.write(unescape("%3Cscript src='{$_PATH.js.core}/jquery{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <style type='text/css'>
      .container {
        width:800px;
      }
    </style>
    <title>{$title}</title>
  </head>
  <body>
    <nav class='navbar navbar-fixed-top'>
      <div class='navbar-inner'>
        <div class='container'>
          <a href='/install' class='brand'>
            candyCMS - Installation and migration assistant
          </a>
        </div>
      </div>
    </nav>
    <div class='container'>
      <div class='page-header'>
        <h1>
          {$title}
        </h1>
      </div>
      {$content}
    </div>
  </body>
</html>
