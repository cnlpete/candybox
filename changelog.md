# Changelog

## 4.1

### Changes
- renamed app/assets/stylesheets/core/application.less and app/assets/stylesheets/mobile/application.less to app/assets/stylesheets/core.less and app/assets/stylesheets/mobile.less
- added SHA512 hash for passwords. Old md5 passwords are still supported, but no longer generated
- optimized SQL types and lengths for various server settings and IPv6
- API tokens are not allowed by default. They must be enabled in the config (ALLOW_API_TOKENS)
- removed Smarty-{strip} strip and use outputfilter plugin instead
- always provide action as class to main container class