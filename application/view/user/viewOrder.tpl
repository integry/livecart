{% extends "layout/frontend.tpl" %}

{% block title %}{t _view_order} [[order.invoiceNumber]] ([[order.formatted_dateCompleted.date_long]]){% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "ordersMenu"]) ]]
{% block content %}

	<label class="title">{t _order_id}:</label>
	<label class="text">[[order.invoiceNumber]]</label>
	<div class="clear"></div>

	<label class="title">{t _placed}:</label>
	<label class="text">[[order.formatted_dateCompleted.date_long]]</label>
	<div class="clear"></div>

	<label class="title">{t _order_total}:</label>
	<label class="text">{order.formattedTotal[order.Currency.ID]}</label>
	<div class="clear"></div>

	<label class="title">{t _order_status}:</label>
	<label class="text">[[ partial('user/orderStatus.tpl', ['order': order]) ]]</label>
	<div class="clear"></div>

	{% if order.isRecurring %}
		<label class="title"></label>
		<label class="text">
			{% if subscriptionStatus > 0 %}
				{t _active_subscription}
			{% else %}
				{t _inactive_subscription}
			{% endif %}
		</label>
		<div class="clear"></div>

		{* Rebills every x months *}
		{foreach from=recurringProductPeriodsByItemId item="period"}
			<label class="title"></label>
			{% if period.periodLength == 1 %}
				{assign var="length" value=''}
				{capture name="a" assign="period"}{t `periodTypesSingle[period.periodType]`}{/capture}
			{% else %}
				{% set length = period.periodLength %}
				{capture name="a" assign="period"}{t `periodTypesPlural[period.periodType]`}{/capture}
			{% endif %}
			<label class="text">{maketext text=_rebills_every params="`length`,`period`"}</label>
			<div class="clear"></div>
		{% endfor %}

		{% if !empty(nextRebillDate) %}
			<label class="title">{t _next_rebill}:</label>
			<label class="text">[[nextRebillDate.date_medium]]</label>
			<div class="clear"></div>
		{% endif %}

		<label class="title">{t _remaining_rebills}:</label>
		<label class="text">{% if order.rebillsLeft != -1 %}[[order.rebillsLeft]]{% else %}{t _remaining_rebills_till_canceled}{% endif %}
			{% if !empty(canCancelRebills) %}
				<span class="cancelFurtherRebills">
					{% if currentPage > 1 %}
						{assign var='rebillQuery' value="page=`currentPage`"}
					{% else %}
						{assign var='rebillQuery' value=''}
					{% endif %}
					<a href="[[ url("user/cancelFurtherRebills/" ~ order.ID, "rebillQuery") ]]" onclick="return confirm('{t _are_you_sure_want_to_cancel_subscription}');" />{t _cancel_this_subscription}</a>
				</span>
			{% endif %}
		</label>
		<div class="clear"></div>
	{% endif %}

	<p>
		{% if !order.isCancelled && !config('DISABLE_INVOICES') %}
			<a href="[[ url("user/orderInvoice/" ~ order.ID) ]]" target="_blank" class="invoice">{t _order_invoice}</a>
		{% endif %}
		<a href="[[ url("user/reorder/" ~ order.ID) ]]" class="reorder">{t _reorder}</a>
	</p>

	{foreach from=order.shipments item="shipment" name="shipments"}
		{% if shipment.items %}

			{% if !shipment.isShippable %}
				<h2>{t _downloads}</h2>
			{% elseif smarty.foreach.shipments.total > 1 %}
				<h2>{t _shipment} #[[smarty.foreach.shipments.iteration]]</h2>
				<p>
					{t _status}: [[ partial('user/shipmentStatus.tpl', ['shipment': shipment]) ]]
				</p>
			{% else %}
				<h2>{t _ordered_products}</h2>
			{% endif %}
			[[ partial('user/shipmentEntry.tpl', ['downloadLinks': true]) ]]

		{% endif %}
	{% endfor %}

	{function name="address"}
		{% if !empty(address) %}
			<p>
				[[address.fullName]]
			</p>
			<p>
				[[address.companyName]]
			</p>
			<p>
				[[address.address1]]
			</p>
			<p>
				[[address.address2]]
			</p>
			<p>
				[[address.city]]
			</p>
			<p>
				{% if address.stateName %}[[address.stateName]], {% endif %}[[address.postalCode]]
			</p>
			<p>
				[[address.countryName]]
			</p>
			<p>
				[[ partial('order/addressFieldValues.tpl', ['showLabels': false]) ]]
			</p>
		{% endif %}
	{/function}

	[[ partial("order/fieldValues.tpl") ]]

	<div id="overviewAddresses">order.shipments

		{% if order.ShippingAddress && !order.isMultiAddress %}
		<div class="addressContainer">
			<h3>{t _is_shipped_to}:</h3>
			{% if order.isLocalPickup %}
				{foreach order.shipments as shipment}
					<div class="ShippingServiceDescription">
						{shipment.ShippingService.description()|escape}
					</div>
				{% endfor %}

			{% else %}
				{address address=order.ShippingAddress}
			{% endif %}
		</div>
		{% endif %}

		<div class="addressContainer">
			<h3>{t _is_billed_to}:</h3>
			{address address=order.BillingAddress}
		</div>

	</div>

	<div class="clear"></div>

	{% if order.isRecurring && orders %}
		<h2>{t _invoices}</h2>
		[[ partial('user/invoicesTable.tpl', ['itemList': orders, 'paginateAction': "viewOrder", 'textDisplaying': _displaying_invoices, 'textFound': _invoices_found, 'id': order.ID, 'query': 'page=_000_']) ]]
	{% endif %}

	<h2 id="m_s_g">{t _support}</h2>
	<p class="noteAbout">{t _have_questions}</p>

	{% if !empty(notes) %}
	   <ul class="notes">
		   {% for note in notes %}
			   [[ partial('user/orderNote.tpl', ['note': note]) ]]
		   {% endfor %}
	   </ul>
	{% endif %}
	{form action="controller=user action=addNote id=`order.ID`" method=POST id="noteForm" handle=noteForm class="form-horizontal"}
		[[ textareafld('text', '_enter_question') ]]

		[[ partial('block/submit.tpl', ['caption': "_submit_response"]) ]]
	{/form}

{% endblock %}
