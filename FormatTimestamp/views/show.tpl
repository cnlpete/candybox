{* Add and run jQuery.timeago() *}
<script type='text/javascript' src='{$_PATH.js}/plugins/jquery.timeago{$_SYSTEM.compress_files_suffix}.js'></script>
<script type='text/javascript'>
  jQuery.timeago.settings.range = {$range};
  jQuery.timeago.settings.strings = {
    prefixAgo: "{$lang.formattimestamp.prefixAgo}",
    prefixFromNow: "{$lang.formattimestamp.prefixFromNow}",
    suffixAgo: "{$lang.formattimestamp.suffixAgo}",
    suffixFromNow: "{$lang.formattimestamp.suffixFromNow}",
    seconds: "{$lang.formattimestamp.seconds}",
    minute: "{$lang.formattimestamp.minute}",
    minutes: "{$lang.formattimestamp.minutes}",
    hour: "{$lang.formattimestamp.hour}",
    hours: "{$lang.formattimestamp.hours}",
    day: "{$lang.formattimestamp.day}",
    days: "{$lang.formattimestamp.days}",
    month: "{$lang.formattimestamp.month}",
    months: "{$lang.formattimestamp.months}",
    year: "{$lang.formattimestamp.year}",
    years: "{$lang.formattimestamp.years}",
    wordSeparator: "{$lang.formattimestamp.wordSeparator}",
  };
  $('time.js-timeago').timeago();
</script>
