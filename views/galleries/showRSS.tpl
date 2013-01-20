<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
    xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>{$WEBSITE_NAME} - {$data.title}</title>
    <description>{$data.content}</description>
    <language>{$WEBSITE_LANGUAGE}</language>
    <link>{$data.url}</link>
    <copyright>{$data.author.full_name}</copyright>
    <pubDate>{if $WEBSITE_MODE !== 'test'}{$smarty.now|date_format:'%a, %d %b %Y %H:%M:%S %z'}{/if}</pubDate>
    <atom:link href="{$CURRENT_URL}" rel="self" type="application/rss+xml" />
    {foreach $data.files as $d}
    <item>
      <title>{$d.file}</title>
      <pubDate>{$d.date.rss}</pubDate>
      <guid isPermaLink="false">{$d.url_popup}</guid>
      <link>{$d.url_popup}</link>
      {if $WEBSITE_MODE !== 'test'}
        <description>
          <![CDATA[
          <img src="{$WEBSITE_URL}/{$d.url_thumb}"
              width="{$d.thumb_width}"
              height="{$d.thumb_height}"
              alt="{$d.file}" />
          {if $d.content}
            <p>{$d.content}</p>
          {/if}
          ]]>
        </description>
      {/if}
      <media:title>{$d.file}</media:title>
      <media:description><![CDATA[{$d.content}]]></media:description>
      <media:thumbnail
        url="{$WEBSITE_URL}/{$d.url_thumb}"
        width="{$d.thumb_width}"
        height="{$d.thumb_height}" />
      <media:content
        url="{$WEBSITE_URL}/{$d.url_popup}"
        height="{$d.popup_height}"
        width="{$d.popup_width}"
        fileSize="{$d.popup_size}"
        type="{$d.popup_mime}" />
    </item>
    {/foreach}
  </channel>
</rss>