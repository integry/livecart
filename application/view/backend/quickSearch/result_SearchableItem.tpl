<a href="javascript:void(0);"
	onclick="if(window.settings){var s=window.settings, t=s.treeBrowser, i = '{$record.meta.section_id|escape}'; s.activateCategory(i); t.openItem(i); t.selectItem(i);} else {window.location.href='{link controller="backend.settings" query="rt=`$randomToken`"}#section_{$record.meta.section_id|escape}__';}"
>{$record.value|escape|mark_substring:$query}</a>
<span></span>