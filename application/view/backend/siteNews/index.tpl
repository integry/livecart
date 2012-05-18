{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveList.css"}

{includeJs file="backend/SiteNews.js"}
{includeCss file="backend/SiteNews.css"}

{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlCalendar/calendar.js"}
{includeJs file="library/dhtmlCalendar/lang/calendar-en.js"}
{*includeJs file="library/dhtmlCalendar/lang/calendar-`$curLanguageCode`.js"*}
{includeJs file="library/dhtmlCalendar/calendar-setup.js"}
{includeCss file="library/dhtmlCalendar/calendar-win2k-cold-2.css"}

{pageTitle help="content.site"}{t _site_news}{/pageTitle}

{include file="layout/backend/header.tpl"}

{allowed role="news.create"}

	<ul class="menu" id="newsMenu">
		<li class="addNews"><a href="#add" id="addNewsLink">{t _add_news}</a></li>
		<li class="addNewsCancel done" style="display: none;"><a href="#cancel" id="addNewsCancelLink">{t _cancel_adding_news}</a></li>
	</ul>

{/allowed}

<fieldset id="addNews" class="slideForm addForm" style="display: none;">

	<legend>{t _add_news|capitalize}</legend>

	{form action="controller=backend.siteNews action=add" method="POST" onsubmit="new Backend.SiteNews.Add(this); return false;" handle=$form id="newsForm" class="enabled"}
		<input type="hidden" name="id" />

		{input name="time"}
			{label}{t _date}:{/label}
			{calendar id="time"}
		{/input}

		{input name="title"}
			{label}{t _title}:{/label}
			{textfield}
		{/input}

		{input name="text"}
			{label}{t _text}:{/label}
			{textarea class="tinyMCE"}
		{/input}

		{input name="text"}
			{label class="wide"}{t _text}:{/label}
			{textarea class="tinyMCE"}
		{/input}

		{input name="moreText"}
			{label class="wide"}{t _more_text}:{/label}
			{textarea class="tinyMCE"}
		{/input}

		{language}
			{input name="title_`$lang.ID`"}
				{label class="wide"}{t _title}:{/label}
				{textfield}
			{/input}

			{input name="text_`$lang.ID`"}
				{label class="wide"}{t _text}:{/label}
				{textarea class="tinyMCE"}
			{/input}

			{input name="moreText_`$lang.ID`"}
				{label class="wide"}{t _more_text}:{/label}
				{textarea class="tinyMCE"}
			{/input}
		{/language}

		<fieldset class="controls" {denied role="news"}style="display: none;"{/denied}>
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit save" value="{tn _save}" />
			<input type="submit" class="submit add" value="{tn _add}" />
			{t _or} <a class="cancel" href="#" onclick="Backend.SiteNews.prototype.hideAddForm(); return false;">{t _cancel}</a>
		</fieldset>
	{/form}

</fieldset>

<ul id="newsList" class="activeList {allowed role="news.sort"}activeList_add_sort{/allowed} {allowed role="news.delete"}activeList_add_delete{/allowed} {allowed role="news.update"}activeList_add_edit{/allowed}">
</ul>

<div style="display: none">
	<span id="deleteUrl">{link controller="backend.siteNews" action=delete}?id=</span>
	<span id="confirmDelete">{t _del_conf}</span>
	<span id="sortUrl">{link controller="backend.siteNews" action=saveOrder}</span>
	<span id="statusUrl">{link controller="backend.siteNews" action=setEnabled}</span>
	<span id="saveUrl">{link controller="backend.siteNews" action=save}</span>
</div>

<ul style="display: none;">
<li id="newsList_template" style="position: relative;">
	<div>
		<div class="newsListContainer">

			<span class="newsCheckBox"{denied role="news.status"} style="display: none;"{/denied}>
				<input type="checkbox" class="checkbox" name="isEnabled" onclick="this.up('li').handler.setEnabled(this);" />
				<span class="progressIndicator" style="float: left; padding: 0; display: none;"></span>
			</span>

			<span class="progressIndicator" style="display: none; "></span>

			<span class="newsData">
				<span class="newsTitle"></span>
				<span class="newsDate"></span>
				<br class="clear" />
				<span class="newsText"></span>
			</span>

		</div>

		<div class="formContainer activeList_editContainer" style="display: none;"></div>

	</div>
	<div class="clear"></div>
</li>
</ul>

<script type="text/javascript">
	Form.State.backup($("newsForm"));
	new Backend.SiteNews({json array=$newsList}, $('newsList'), $('newsList_template'));
</script>

{include file="layout/backend/footer.tpl"}