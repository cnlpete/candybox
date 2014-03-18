BEGIN:VCALENDAR
VERSION:2.0
X-WR-CALNAME:{$calendar.title}
METHOD:PUBLISH
{if !$ACTIVE_TEST}
PRODID:{$WEBSITE_URL}
{/if}
BEGIN:VEVENT
UID:{$calendar.id}
CREATED:{$calendar.date.raw|date_format:"%Y%m%dT%H%M%SZ"}
SUMMARY:{$calendar.title}
DESCRIPTION:{$calendar.content}
DTSTART;VALUE=DATE:{$calendar.start_date.raw|date_format:"%Y%m%d"}
DTEND;VALUE=DATE:{if $calendar.end_date.raw > 0}{($calendar.end_date.raw+86400)|date_format:"%Y%m%d"}
{else}{($calendar.start_date.raw+86400)|date_format:"%Y%m%d"}
{/if}
DTSTAMP:{$calendar.date.raw|date_format:"%Y%m%dT%H%M%SZ"}
END:VEVENT
END:VCALENDAR