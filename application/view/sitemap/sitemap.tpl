[[ xml ]] version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
	http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
{% for entry in entries %}
<url>
{% for nodename, node in entry %}
	<[[nodename]]>[[node]]</[[nodename]]>
{% endfor %}
</url>
{% endfor %}
</urlset>
