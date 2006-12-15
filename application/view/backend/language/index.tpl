{includeJs file=library/ActiveList.js}
{includeJs file=library/KeyboardEvent.js}
{includeJs file=backend/Language.js}
{includeCss file=library/ActiveList.css}
{includeCss file=backend/Language.css}
{pageTitle help="language"}{t _admin_languages}{/pageTitle}

{include file="layout/header.tpl"}

<script type="text/javascript">
	var lng = new Backend.LanguageIndex();	
	lng.setFormUrl('{link controller=backend.language action=addForm}');
	lng.setAddUrl('{link controller=backend.language action=add}');
	lng.setStatusUrl("{link controller=backend.language action=setEnabled}/");
</script>

<ul class="menu" id="langPageMenu">
	<li><a href="#" onClick="lng.showAddForm(); return false;">{t _add_language}</a></li>
</ul>

<div class="menuLoadIndicator" id="langAddMenuLoadIndicator"></div>
<div id="addLang" class="slideForm"></div>

<br />

<ul id="languageList" class="activeList_add_delete activeList_add_edit">
{foreach from=$languagesList item=item}
	{include file="backend/language/listItem.tpl" showContainer=true}
{/foreach}
</ul>

{literal}
<script type="text/javascript">
    function initLangList()
    {	
		new ActiveList('languageList', {
	         beforeEdit:     function(li) { window.location.href = '{/literal}{link controller=backend.language action=edit}{literal}/' +  this.getRecordId(li); },
	         beforeSort:     function(li, order) 
			 { 
				 return '{/literal}{link controller=backend.language action=saveorder}{literal}?draggedId=' + this.getRecordId(li) + '&' + order 
			   },
	         beforeDelete:   function(li)
	         {
	             if(confirm('{/literal}{tn _confirm_delete}{literal}')) return '{/literal}{link controller=backend.language action=delete}{literal}/' + this.getRecordId(li)
	         },
	         afterEdit:      function(li, response) {  },
	         afterSort:      function(li, response) {  },
	         afterDelete:    function(li, response)  { Element.remove(li); }
	     });
	}	
	initLangList();
</script>
{/literal}

{*
<!-- {maketext text="_statistic_languages_full" params="$count_all,$count_active"}. -->
*}

{include file="layout/footer.tpl"}