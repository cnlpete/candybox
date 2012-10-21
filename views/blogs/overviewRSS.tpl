<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>{$_WEBSITE.title} - {$WEBSITE_NAME}</title>
    <description>{$lang.website.description}</description>
    <language>{$WEBSITE_LANGUAGE}</language>
    <link>{$WEBSITE_URL}</link>
    <copyright>{$WEBSITE_NAME}</copyright>
    <pubDate>{if $WEBSITE_MODE !== 'test'}{$smarty.now|date_format:'%a, %d %b %Y %H:%M:%S %z'}{/if}</pubDate>
    <atom:link href="{$CURRENT_URL}" rel="self" type="application/rss+xml" />
    {foreach $data as $d}
      <item>
        <title>{$d.title}</title>
        <pubDate>{$d.date.rss}</pubDate>
        {if $WEBSITE_MODE !== 'test'}
          <description>
            <![CDATA[
              {if $d.teaser}
                <p>
                  <strong>
                    {$d.teaser}
                  </strong>
                </p>
              {/if}
              {$d.content}
            ]]>
          </description>
        {/if}
        <dc:creator>{$d.author.full_name}</dc:creator>
        <comments>{$d.url}</comments>
        <guid isPermaLink="true">{$d.url}</guid>
        <link>{$d.url}</link>
      </item>
    {/foreach}
  </channel>
</rss>