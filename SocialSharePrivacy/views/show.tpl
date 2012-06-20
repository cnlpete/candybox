<div id='socialshareprivacy'></div>
<script src='{$_PATH.js}/core/jquery.socialshareprivacy{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
<script type='text/javascript'>
  $(document).ready(function(){
    if($('#socialshareprivacy').length > 0){
      $('#socialshareprivacy').socialSharePrivacy({
        services : {
          facebook : {
            'language' : '{$WEBSITE_LOCALE}',
            'dummy_img' : '{$_PATH.images}/jquery.socialshareprivacy/dummy_facebook.png'
          },
          twitter : {
            'dummy_img' : '{$_PATH.images}/jquery.socialshareprivacy/dummy_twitter.png'
          },
          gplus : {
            'dummy_img' : '{$_PATH.images}/jquery.socialshareprivacy/dummy_gplus.png',
            'display_name' : 'Google Plus'
          }
        },
        css_path : ''
      });
    };
  });
</script>