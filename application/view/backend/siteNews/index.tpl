{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveList.css"}

{includeJs file="backend/SiteNews.js"}
{includeCss file="backend/SiteNews.css"}

{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlCalendar/calendar.js"}
{includeJs file="library/dhtmlCalendar/lang/calendar-en.js"}
{includeJs file="library/dhtmlCalendar/lang/calendar-`$curLanguageCode`.js"}
{includeJs file="library/dhtmlCalendar/calendar-setup.js"}
{includeCss file="library/dhtmlCalendar/calendar-win2k-cold-2.css"}

{pageTitle help="sitenews"}{t _site_news}{/pageTitle}

{include file="layout/backend/header.tpl"}

<div id="confirmations" class="rightConfirmations"></div>

<ul class="menu" id="newsMenu">
	<li class="addNews"><a href="#add" id="addNewsLink">{t _add_news}</a></li>
	<li class="addNewsCancel" style="display: none;"><a href="#cancel" id="addNewsCancelLink">{t _cancel_adding_news}</a></li>
</ul>

<fieldset id="addNews" class="slideForm addForm" style="display: none;">

	<legend>{t _add_news}</legend>

	{form action="controller=backend.siteNews action=save" method="POST" onsubmit="new Backend.SiteNews.Add(this); return false;" handle=$form id="newsForm"}
		<input type="hidden" name="id" />

		<p>
			<label>{t _date}</label>
			{calendar name="time" id="time"}		
		</p>
		<p>
			{{err for="title"}}
				<label class="wide">{t _title}:</label>
				{textfield class="text"}
			{/err}	
		</p>
		<p>
			{{err for="text"}}
				<label class="wide">{t _text}:</label>
				{textarea class="tinyMCE"}
			{/err}
		</p>
		<p>
			<label class="wide">{t _more_text}:</label>
			{textarea name="moreText" class="tinyMCE"}
		</p>
	
		{language}
			<p>
				<label class="wide">{t _title}:</label>
				{textfield class="text" name="title_`$lang.ID`"}
			</p>
			<p>
				<label class="wide">{t _text}:</label>
				{textarea name="text_`$lang.ID`" class="tinyMCE"}
			</p>
			<p>
				<label class="wide">{t _more_text}:</label>
				{textarea name="moreText_`$lang.ID`" class="tinyMCE"}
			</p>
		{/language}
		
		<fieldset class="controls" {denied role="news"}style="display: none;"{/denied}>
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit save" value="{tn _save}" />
			<input type="submit" class="submit add" value="{tn _add}" />
			{t _or} <a class="cancel" href="#" onclick="Backend.SiteNews.prototype.hideAddForm(); return false;">{t _cancel}</a>
		</fieldset>
	{/form}
	
</fieldset>

<ul id="newsList" class="activeList activeList_add_sort activeList_add_delete activeList_add_edit">
</ul>

<div style="display: none">
	<span id="deleteUrl">{link controller=backend.siteNews action=delete}?id=</span>
	<span id="confirmDelete">{t _del_conf}</span>
	<span id="sortUrl">{link controller=backend.siteNews action=saveOrder}</span>
</div>

<ul style="display: none;">
<li id="newsList_template" class="activeList_add_sort activeList_add_delete" style="position: relative;">
	<div>
		<div class="newsListContainer">

			<span class="newsCheckBox">
				<input type="checkbox" class="checkbox" onclick="lng.setEnabled(this);" />
			</span>	
            
		    <span class="progressIndicator" style="display: none;"></span>
		
			<span class="newsData">
				<span class="newsTitle"></span> 
				<span class="newsDate"></span> 
				<br class="clear" />
				<span class="newsText"></span> 
			</span>
											
		</div>
		
		<div class="formContainer"></div>
		
	</div>			
	<div class="clear"></div>
</li>
</ul>

<script type="text/javascript">
    Form.State.backup($("newsForm"));
	new Backend.SiteNews({json array=$newsList}, $('newsList'), $('newsList_template'));
</script>

{include file="layout/backend/footer.tpl"}