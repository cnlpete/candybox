<div id='socialshareprivacy'></div>
<link href='{$_PATH.plugins}/SocialSharePrivacy/assets/css/socialshareprivacy{$_SYSTEM.compress_files_suffix}.css'
      rel='stylesheet' type='text/css' media='screen, projection'/>
<script src='{$_PATH.plugins}/SocialSharePrivacy/assets/js/jquery.socialshareprivacy{$_SYSTEM.compress_files_suffix}.js' type='text/javascript'></script>
<script type='text/javascript'>
  $(document).ready(function(){
    if($('#socialshareprivacy').length > 0){
      $('#socialshareprivacy').socialSharePrivacy({
        services : {
          facebook : {
            'language' : '{$WEBSITE_LOCALE}',
            'dummy_img' : '{$_PATH.plugins}/SocialSharePrivacy/assets/images/dummy_facebook.png'
          },
          twitter : {
            'dummy_img' : '{$_PATH.plugins}/SocialSharePrivacy/assets/images/dummy_twitter.png'
          },
          gplus : {
            'dummy_img' : '{$_PATH.plugins}/SocialSharePrivacy/assets/images/dummy_gplus.png',
            'display_name' : 'Google Plus'
          }
        },
        css_path : ''
      });
    };
  });
</script>