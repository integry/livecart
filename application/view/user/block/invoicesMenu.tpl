{% if $hasInvoices %}
	<li id="invoicesMenu" class="{% if "invoicesMenu" == $current %}selected{% endif %}"><a href="{link controller=user action=invoices}">{t _invoices}</a></li>
{% endif %}

{% if $hasPendingInvoices %}
	<li id="pendingInvoicesMenu" class="{% if "pendingInvoicesMenu" == $current %}selected{% endif %}"><a href="{link controller=user action=pendingInvoices}">{t _pending_invoices}</a></li>
{% endif %}
