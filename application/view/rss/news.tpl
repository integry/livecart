{php}ob_end_clean();{/php}
<rss version="2.0">
	<channel>
		<title>{t _news}</title>     
		<link>{link controller=news action=index url=true}</link>
		<description></description>
		{foreach from=$feed item=entry}
			<item>
				<title><![CDATA[{$entry.title_lang|utf8_decode}]]></title>
				<link><![CDATA[{newsUrl news=$entry full=true}]]></link> 
				<description><![CDATA[{$entry.text_lang|utf8_decode}]]></description>
			</item>
		{/foreach}
	</channel>
</rss>