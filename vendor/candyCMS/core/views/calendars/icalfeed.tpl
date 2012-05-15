BEGIN:VCALENDAR
VERSION:2.0
X-WR-CALNAME:{$calendar.title}
METHOD:PUBLISH
PRODID:{$WEBSITE_URL}
{foreach $calendar as $c}
{foreach $c.dates as $d}
BEGIN:VEVENT
UID:{$d.id}
CREATED:{$d.date.w3c}
SUMMARY:{$d.title}
DESCRIPTION:{$d.content}
DTSTART;VALUE=DATE:{$d.start_date.raw|date_format:"%Y%m%d"}
DTEND;VALUE=DATE:{if $d.end_date.raw > 0}{($d.end_date.raw+86400)|date_format:"%Y%m%d"}
{else}{($d.start_date.raw+86400)|date_format:"%Y%m%d"}
{/if}
DTSTAMP:{$d.date.w3c}
END:VEVENT
{/foreach}
{/foreach}
END:VCALENDAR