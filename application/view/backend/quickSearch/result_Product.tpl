<a href="{link controller="backend.category query="rt=`$randomToken`"}#product_[[record.ID]]__" onclick="try {return footerToolbar.tryToOpenItemWithoutReload([[record.ID]],'product');} catch(e){}">{$record.name_lang|mark_substring:$query}</a>
<span>({t _sku}: {$record.sku|mark_substring:$query})</span>
