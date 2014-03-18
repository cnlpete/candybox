#!/bin/bash

GITSPLIT=tools/gitsplit/git_split.sh
RO_GITDIR=/home/$USER/tmp
SRC_REPO=$(pwd)

## Plugins
$GITSPLIT $SRC_REPO plugins/AddThis $RO_GITDIR/candybox-plugin-addthis
$GITSPLIT $SRC_REPO plugins/Analytics $RO_GITDIR/candybox-plugin-analytics
$GITSPLIT $SRC_REPO plugins/Archive $RO_GITDIR/candybox-plugin-archive
$GITSPLIT $SRC_REPO plugins/Bbcode $RO_GITDIR/candybox-plugin-bbcode
$GITSPLIT $SRC_REPO plugins/Cronjob $RO_GITDIR/candybox-plugin-cronjob
$GITSPLIT $SRC_REPO plugins/Disqus $RO_GITDIR/candybox-plugin-disqus
$GITSPLIT $SRC_REPO plugins/FacebookCMS $RO_GITDIR/candybox-plugin-facebook
$GITSPLIT $SRC_REPO plugins/FormatTimestamp $RO_GITDIR/candybox-plugin-formattimestamp
$GITSPLIT $SRC_REPO plugins/Headlines $RO_GITDIR/candybox-plugin-headlines
$GITSPLIT $SRC_REPO plugins/LanguageChooser $RO_GITDIR/candybox-plugin-languagechooser
$GITSPLIT $SRC_REPO plugins/Markdown $RO_GITDIR/candybox-plugin-markdown
$GITSPLIT $SRC_REPO plugins/Piwik $RO_GITDIR/candybox-plugin-piwik
$GITSPLIT $SRC_REPO plugins/Recaptcha $RO_GITDIR/candybox-plugin-recaptcha
$GITSPLIT $SRC_REPO plugins/Smushit $RO_GITDIR/candybox-plugin-smushit
$GITSPLIT $SRC_REPO plugins/Snippets $RO_GITDIR/candybox-plugin-snippets
$GITSPLIT $SRC_REPO plugins/SocialSharePrivacy $RO_GITDIR/candybox-plugin-socialshareprivacy
$GITSPLIT $SRC_REPO plugins/TagCloud $RO_GITDIR/candybox-plugin-tagcloud
$GITSPLIT $SRC_REPO plugins/TinyMCE $RO_GITDIR/candybox-plugin-tinymce

