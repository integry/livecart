<a href="javascript:void(0);"
	onclick="if(window.settings){literal}{var s=window.settings, t=s.treeBrowser, i = '{/literal}{$record.meta.section_id|escape}{literal}'; s.activateCategory(i); t.openItem(i); t.selectItem(i);} else {window.location.href='{/literal}{link controller="backend.settings" query="rt=`$randomToken`"}#section_{$record.meta.section_id|escape}{literal}__';}{/literal}"
>{$record.value|escape|mark_substring:$query}</a>
<span></span>