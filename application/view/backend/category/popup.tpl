{includeJs file="library/livecart.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/Product.js"}
{includeCss file="backend/Category.css"}
{include file="backend/category/loadJsTree.tpl"}

{% block title %}{t _select_category}{{% endblock %}

{include file="layout/backend/meta.tpl"}

{literal}
<style>
	body
	{
		background-image: none;
	}
</style>
{/literal}

<div id="popupCategoryContainer" class="treeContainer">

	<div style="font-weight: bold; padding: 5px; font-size: larger;">{t _select_category}:</div>

	<div id="categoryBrowser" class="treeBrowser"> </div>

	<fieldset class="controls" style="margin-top: 0.2em;">
		<input type="button" class="submit" id="select" value="{tn _proceed}" />
		{t _or}
		<a href="#cancel" id="cancel" class="cancel">{t _cancel}</a>
	</fieldset>

</div>

{literal}
<script type="text/javascript">
	if (window.opener.popupOnload())
	{
		window.onload = window.opener.popupOnload;
	}
{/literal}
	Backend.Category.init({json array=$categoryList});
</script>

</body>
</html>