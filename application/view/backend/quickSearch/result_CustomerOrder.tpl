<a href="{link controller=backend.customerOrder query="rt=`$randomToken`"}#order_{$record.ID}__">{$record.invoiceNumber|escape|mark_substring:$query}</a>
<span>({$record.formattedTotalAmount})</span>