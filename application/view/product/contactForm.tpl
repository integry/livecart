<div id="contactFormSection" class="productSection contactFormSection">
<h2>{t _inquire}<small>{t _inquire_title}</small></h2>

<div>
{form action="product/sendContactForm" method="POST" handle=$contactForm id="productContactForm" onsubmit="new Product.ContactForm(this); return false;" class="form-horizontal"}
	[[ textfld('name', '_inquiry_name') ]]

	{* anti-spam *}
	<div style="display: none;">
		[[ textfld('surname', 'surname') ]]
	</div>

	[[ textfld('email', '_inquiry_email') ]]

	[[ textareafld('msg', '_inquiry_msg') ]]

	[[ partial('block/submit.tpl', ['caption': "_form_submit"]) ]]

	<input type="hidden" name="id" value="[[product.ID]]" />

{/form}
<div class="clear"></div>
</div>
</div>
