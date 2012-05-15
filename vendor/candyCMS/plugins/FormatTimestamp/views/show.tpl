{* Add and run jQuery.timeago() *}
<script type='text/javascript' src='{$_PATH.js}/plugin/jquery.timeago{$_SYSTEM.compress_files_suffix}.js'></script>
<script type='text/javascript'>
  jQuery.timeago.settings.strings = {
    prefixAgo: "{$lang.global.time.formattimestamp.prefixAgo}",
    prefixFromNow: "{$lang.global.time.formattimestamp.prefixFromNow}",
    suffixAgo: "{$lang.global.time.formattimestamp.suffixAgo}",
    suffixFromNow: "{$lang.global.time.formattimestamp.suffixFromNow}",
    seconds: "{$lang.global.time.formattimestamp.seconds}",
    minute: "{$lang.global.time.formattimestamp.minute}",
    minutes: "{$lang.global.time.formattimestamp.minutes}",
    hour: "{$lang.global.time.formattimestamp.hour}",
    hours: "{$lang.global.time.formattimestamp.hours}",
    day: "{$lang.global.time.formattimestamp.day}",
    days: "{$lang.global.time.formattimestamp.days}",
    month: "{$lang.global.time.formattimestamp.month}",
    months: "{$lang.global.time.formattimestamp.months}",
    year: "{$lang.global.time.formattimestamp.year}",
    years: "{$lang.global.time.formattimestamp.years}",
    wordSeparator: "{$lang.global.time.formattimestamp.wordSeparator}",
  };
  $('time.js-timeago').timeago();
</script>
