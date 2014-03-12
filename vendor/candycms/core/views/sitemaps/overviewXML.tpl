<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>{$_website_landing_page_}</loc>
    <priority>1.0</priority>
    <changefreq>hourly</changefreq>
  </url>
  <url>
    <loc>{$WEBSITE_URL}/newsletters</loc>
    <priority>0.1</priority>
    <changefreq>never</changefreq>
  </url>
  <url>
    <loc>{$WEBSITE_URL}/sitemaps</loc>
    <priority>0.75</priority>
    <changefreq>daily</changefreq>
  </url>
  <url>
    <loc>{$WEBSITE_URL}/searches</loc>
    <priority>0.1</priority>
    <changefreq>never</changefreq>
  </url>
  <url>
    <loc>{$WEBSITE_URL}/sessions/create</loc>
    <priority>0.1</priority>
    <changefreq>never</changefreq>
  </url>
  <url>
    <loc>{$WEBSITE_URL}/sessions/password</loc>
    <priority>0.1</priority>
    <changefreq>never</changefreq>
  </url>
  <url>
    <loc>{$WEBSITE_URL}/sessions/verification</loc>
    <priority>0.1</priority>
    <changefreq>never</changefreq>
  </url>
  <url>
    <loc>{$WEBSITE_URL}/users/create</loc>
    <priority>0.1</priority>
    <changefreq>never</changefreq>
  </url>
  {if $blogs}
    {foreach $blogs as $b}
      <url>
        <loc>{$b.url}</loc>
        <priority>{$b.priority}</priority>
        <changefreq>{$b.changefreq}</changefreq>
        <lastmod>{$b.date.w3c_date}</lastmod>
      </url>
    {/foreach}
  {/if}
  {if $contents}
    {foreach $contents as $c}
      <url>
        <loc>{$c.url}</loc>
        <priority>{$c.priority}</priority>
        <changefreq>{$c.changefreq}</changefreq>
        <lastmod>{$c.date.w3c_date}</lastmod>
      </url>
    {/foreach}
  {/if}
  {if $galleries}
    {foreach $galleries as $g}
      <url>
        <loc>{$g.url}</loc>
        <priority>{$g.priority}</priority>
        <changefreq>{$g.changefreq}</changefreq>
        <lastmod>{$g.date.w3c_date}</lastmod>
      </url>
    {/foreach}
  {/if}
</urlset>