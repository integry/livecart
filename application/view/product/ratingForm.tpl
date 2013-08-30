{% if 'ENABLE_RATINGS'|config && !$isRated %}
<div id="ratingSection" class="productSection ratingSection">
<h2>{maketext text="_rate_product_name" params=$product.name_lang}<small>{t _rate}</small></h2>
<div id="rateProduct">
	{% if !empty(isLoginRequiredToRate) %}
		<p>{maketext text=_msg_rating_login_required params=[[ url("user/login") ]]}</p>
	{% elseif !empty(isPurchaseRequiredToRate) %}
		<p>{t _msg_rating_purchase_required}</p>
	{% else %}
		[[ partial("product/rate.tpl") ]]
	{% endif %}
</div>
</div>
{% endif %}
