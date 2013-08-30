{% if !empty(hasInvoices) %}
	<li id="invoicesMenu" class="{% if "invoicesMenu" == $current %}selected{% endif %}"><a href="[[ url("user/invoices") ]]">{t _invoices}</a></li>
{% endif %}

{% if !empty(hasPendingInvoices) %}
	<li id="pendingInvoicesMenu" class="{% if "pendingInvoicesMenu" == $current %}selected{% endif %}"><a href="[[ url("user/pendingInvoices") ]]">{t _pending_invoices}</a></li>
{% endif %}
