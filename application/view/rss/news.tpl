{php}ob_end_clean();{/php}
<rss version="2.0">
	<channel>
		<title>{t _news}</title>     
		<link>[[ fullurl("news/index") ]]</link>
		<description></description>
		{foreach from=$feed item=entry}
			<item>
				<title><![CDATA[[[entry.title_lang]]]]></title>
				<link><![CDATA[{newsUrl news=$entry full=true}]]></link> 
				<description><![CDATA[[[entry.text_lang]]]]></description>
			</item>
		{/foreach}
	</channel>
</rss>