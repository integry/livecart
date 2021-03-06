<div id="contactFormSection" class="productSection contactFormSection">
<h2>{t _inquire}<small>{t _inquire_title}</small></h2>

<div>
{form action="controller=product action=sendContactForm" method="POST" handle=$contactForm id="productContactForm" onsubmit="new Product.ContactForm(this); return false;" class="form-horizontal"}
	{input name="name"}
		{label}{t _inquiry_name}:{/label}
		{textfield}
	{/input}

	{* anti-spam *}
	<div style="display: none;">
		{input name="surname"}
			{label}{t surname}:{/label}
			{textfield}
		{/input}
	</div>

	{input name="email"}
		{label}{t _inquiry_email}:{/label}
		{textfield}
	{/input}

	{input name="msg"}
		{label}{t _inquiry_msg}:{/label}
		{textarea}
	{/input}

	{include file="block/submit.tpl" caption="_form_submit"}

	<input type="hidden" name="id" value="{$product.ID}" />

{/form}
<div class="clear"></div>
</div>
</div>
