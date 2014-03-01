{php}ob_end_clean();{/php}
<rss version="2.0">
	<channel>
		<title>{t _news}</title>     
		<link>[[ fullurl("news/index") ]]</link>
		<description></description>
		{% for entry in feed %}
			<item>
				<title><![CDATA[[[entry.title()]]]]></title>
				<link><![CDATA[{newsUrl news=entry full=true}]]></link>
				<description><![CDATA[[[entry.text()]]]]></description>
			</item>
		{% endfor %}
	</channel>
</rss>