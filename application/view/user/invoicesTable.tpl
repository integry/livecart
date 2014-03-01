{% if count && perPage %}
	<div class="resultStats">
		{% if !empty(itemList) %}
			{% if count > perPage %}
				{maketext text=textDisplaying params="`from`,`to`,`count`"}
			{% else %}
				{maketext text=textFound params=count}
			{% endif %}
		{% else %}
			{t _no_invoices_found}
		{% endif %}
	</div>
{% endif %}

{% if !empty(itemList) %}
	<table class="invoiceTable table table-striped">
		<thead>
			<tr>
				<th class="number">{t _invoice_number}</th>
				<th class="amount">{t _invoice_amount}</th>
				<th class="date">{t _invoice_date}</th>
				<th class="status">{t _invoice_status}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=itemList item="invoice" name="invoices"}
			<tr>
				<td class="number">{invoice.invoiceNumber|escape}</td>
				<td class="amount">{invoice.formatted_totalAmount|escape}</td>
				<td class="date">{invoice.formatted_dateDue.date_medium|escape}</td>
				{% if invoice.isPaid %}
					<td class="paid status">
						{t _invoice_status_paid}
					</td>
				{% else %}
					<td class="unpaid status">
						{% if invoice.overdue %}
							{t _overdue}
						{% else %}
							{t _invoice_status_unpaid}
						{% endif %}
					</td>
				{% endif %}
			</tr>
		{% endfor %}

		<tr>
			<td colspan="4">
			{% if pendingInvoiceCount && pendingInvoiceCount  > 0 %}
				<a style="float:right;" href="[[ url("user/pendingInvoices") ]]">{maketext text=_x_pending_invoices params=pendingInvoiceCount}</a>
			{% endif %}
			</td>
		</tr>
		</tbody>
	</table>
{% endif %}

{% if count > perPage %}
	{capture assign="url"}{link controller=user action=paginateAction id=id query=query}{/capture}
	{paginate current=currentPage count=count perPage=perPage url=url}
{% endif %}
