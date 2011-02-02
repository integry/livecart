<a href="{link controller=backend.category query="rt=`$randomToken`"}#product_{$record.ID}__" onclick="try {literal}{{/literal}return footerToolbar.tryToOpenItemWithoutReload({$record.ID},'product');{literal}}{/literal} catch(e){literal}{}{/literal}">{$record.name_lang|mark_substring:$query}</a>
<span>({t _sku}: {$record.sku|mark_substring:$query})</span>
