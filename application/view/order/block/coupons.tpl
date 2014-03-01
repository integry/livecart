{% if !empty(isCouponCodes) %}
	<tr id="couponCodes">
		<td colspan="{math equation="extraColspanSize + 5"}">
			{t _have_coupon}: <input type="text" class="text coupon" name="coupon" /> <input type="submit" class="submit coupon" value="{tn _add_coupon}" />
			{% if cart.coupons %}
				<p class="appliedCoupons">
					{t _applied_coupons}:
					{foreach from=cart.coupons item=coupon name=coupons}
						<strong>[[coupon.couponCode]]</strong>{% if !smarty.foreach.coupons.last %}, {% endif %}
					{% endfor %}
				</p>
			{% endif %}
		</td>
	<tr>
{% endif %}