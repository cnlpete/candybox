BEGIN:VCALENDAR
VERSION:2.0
X-WR-CALNAME:{$calendar.title}
METHOD:PUBLISH
PRODID:{$WEBSITE_URL}
BEGIN:VEVENT
UID:{$calendar.id}
CREATED:{$calendar.date.w3c}
SUMMARY:{$calendar.title}
DESCRIPTION:{$calendar.content}
DTSTART;VALUE=DATE:{$calendar.start_date.raw|date_format:"%Y%m%d"}
DTEND;VALUE=DATE:{if $calendar.end_date.raw > 0}{($calendar.end_date.raw+86400)|date_format:"%Y%m%d"}
{else}{($calendar.start_date.raw+86400)|date_format:"%Y%m%d"}
{/if}
DTSTAMP:{$calendar.date.w3c}
END:VEVENT
END:VCALENDAR