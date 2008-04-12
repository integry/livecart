{pageTitle}{t _view_order} #{$order.ID}{/pageTitle}
{loadJs form=true}
<div class="userViewOrder">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

	<h1>{t _view_order} #{$order.ID} ({$order.formatted_dateCompleted.date_long})</h1>

	{include file="user/userMenu.tpl" current="ordersMenu"}

	<div id="userContent">

		<fieldset class="container">

		<label class="title">{t _order_id}:</label>
		<label class="text">{$order.ID}</label>
		<div class="clear"></div>

		<label class="title">{t _placed}:</label>
		<label class="text">{$order.formatted_dateCompleted.date_long}</label>
		<div class="clear"></div>

		<label class="title">{t _order_total}:</label>
		<label class="text">{$order.formattedTotal[$order.Currency.ID]}</label>
		<div class="clear"></div>

		<label class="title">{t _order_status}:</label>
		<label class="text">{include file="user/orderStatus.tpl" order=$order}</label>
		<div class="clear"></div>

		{if !$order.isCancelled}
		<p>
			<a href="{link controller=user action=orderInvoice id=`$order.ID`}" target="_blank" class="invoice">{t _order_invoice}</a>
		</p>
		{/if}

		{foreach from=$order.shipments item="shipment" name="shipments"}

			{if $shipment.items}

				{if !$shipment.isShippable}
					<h2>{t _downloads}</h2>
				{elseif $smarty.foreach.shipments.total > 1}
					<h2>{t _shipment} #{$smarty.foreach.shipments.iteration}</h2>
					<p>
						{t _status}: {include file="user/shipmentStatus.tpl" shipment=$shipment}
					</p>
				{else}
					<h2>{t _ordered_products}</h2>
				{/if}

				{include file="user/shipmentEntry.tpl}

			{/if}

		{/foreach}

		{defun name="address"}
			{if $address}
				<p>
					{$address.fullName}
				</p>
				<p>
					{$address.companyName}
				</p>
				<p>
					{$address.address1}
				</p>
				<p>
					{$address.address2}
				</p>
				<p>
					{$address.city}
				</p>
				<p>
					{if $address.stateName}{$address.stateName}, {/if}{$address.postalCode}
				</p>
				<p>
					{$address.countryName}
				</p>
			{/if}
		{/defun}

		<div id="overviewAddresses">

			{if $order.ShippingAddress}
			<div class="addressContainer">
				<h3>{t _is_shipped_to}:</h3>
				{fun name="address" address=$order.ShippingAddress}
			</div>
			{/if}

			<div class="addressContainer">
				<h3>{t _is_billed_to}:</h3>
				{fun name="address" address=$order.BillingAddress}
			</div>

		</div>

		</fieldset>

		<h2 id="msg">{t _support}</h2>

		<p class="noteAbout">{t _have_questions}</p>

		{if $notes}
		   <ul class="notes">
			   {foreach from=$notes item=note}
				   {include file="user/orderNote.tpl" note=$note}
			   {/foreach}
		   </ul>
		{/if}

		{form action="controller=user action=addNote id=`$order.ID`" method=POST id="noteForm" handle=$noteForm}
		   {err for="text"}
			   {{label {t _enter_question}:}}
			   {textarea}
		   {/err}
		   <input type="submit" class="submit" value="{tn _submit_response}" />
		{/form}

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>