{loadScriptaculous}
{includeJs file=library/ActiveList.js}
{includeJs file=library/KeyboardEvent.js}
{includeJs file=backend/Language.js}
{includeCss file=library/ActiveList.css}
{includeCss file=backend/Language.css}
{pageTitle help="language"}{t _admin_languages}{/pageTitle}

{include file="layout/header.tpl"}

{pageMenu id=pageMenu}
	{menuItem}
		{menuCaption}{t _add_language}{/menuCaption}
		{pageAction}slideForm('addLang', 'pageMenu'){/pageAction}
 	{/menuItem}
{/pageMenu}

<script type="text/javascript">
	var lng = new Backend.LanguageIndex();	
	lng.setAddUrl('{link controller=backend.language action=add}');
	lng.setStatusUrl("{link controller=backend.language action=setEnabled}/");
</script>

<div id="addLang" class="slideForm" style="display:none;"onFocus="document.getElementById('addLang-sel').focus();">
	<div>	
		<form onSubmit="lng.add(this.getElementsByTagName('select')[0].value); return false;" action="">
			<select name="new_language" id="addLang-sel" style="width: 200px" tabIndex=3 onKeyDown="{literal}key = new KeyboardEvent(event); if (key.getKey() == key.KEY_ENTER) {this.form.submit();} {/literal} return false;">
			   {html_options options=$languages_select}
			</select>
			<img src="image/indicator.gif" id="addLangFeedback">
			<input type="submit" value="{t _add_lang_button}" name="sm" tabIndex=4>
			{t _or} <a href="#" onClick="restoreMenu('addLang', 'pageMenu'); return false;">{t _cancel}</a>
		</form>	
	</div>
</div>

<br />

{literal}
<style>
.enabled_0 {color: #AAAAAA;}
.enabled_1 {}
.listSortHover {background-color: #DDDDDD;}
</style>
{/literal}

<ul id="languageList" class="activeList_add_delete">
{foreach from=$languagesList item=item}
	{include file="backend/language/listItem.tpl" showContainer=true}
{/foreach}
</ul>

{literal}
<script type="text/javascript">
    function initLangList()
    {	
		new ActiveList('languageList', {
	         beforeEdit:     function(li) { return 'sort.php?' },
	         beforeSort:     function(li, order) 
			 { 
				 return '{/literal}{link controller=backend.language action=saveorder}{literal}?draggedId=' + this.getRecordId(li) + '&' + order 
			   },
	         beforeDelete:   function(li)
	         {
	             if(confirm('{/literal}{t _confirm_delete}{literal}')) return '{/literal}{link controller=backend.language action=delete}{literal}/' + this.getRecordId(li)
	         },
	         afterEdit:      function(li, response) {  },
	         afterSort:      function(li, response) {  },
	         afterDelete:    function(li, response)  { Element.remove(li); },
	     });
	}	
	initLangList();
</script>
{/literal}

{*
<!-- {maketext text="_statistic_languages_full" params="$count_all,$count_active"}. -->
*}

{include file="layout/footer.tpl"}