[[ xml ]] version='1.0' encoding='UTF-8'?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
	http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">
{% for map in maps %}
	<sitemap>
{% for nodename, node in map %}
		<[[nodename]]>[[node]]</[[nodename]]>
{% endfor %}
	</sitemap>
{% endfor %}
</sitemapindex>
