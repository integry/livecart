{% if 'ENABLE_PRODUCT_SHARING'|config
	&&
	($user.ID || 'ENABLE_ANONYMOUS_PRODUCT_SHARING'|config)
 %}

	<div id="sharingSection" class="productSection sharingSection">
		<h2>{maketext text="_share_product_name"}</h2>

		<div id="sendToFriendRepsonse"></div>

		<div id="shareProduct">
			{form action="controller=product action=sendToFriend id=`$product.ID`" handle=$sharingForm method="POST"
				onsubmit="new Product.Share(this); return false;" class="form-horizontal"}
				<div class="producSharingForm">
					[[ textfld('friendemail', '_friend_email') ]]

					{% if !$user.ID %}
						[[ textfld('nickname', '_nickname') ]]
					{% endif %}

					[[ textareafld('friendemail', '_notes') ]]
				</div>

				{block SEND-TO-FRIEND-SUBMIT}

				[[ partial('block/submit.tpl', ['caption': "_send_to_friend"]) ]]
			{/form}
		</div>
	</div>
	<script type="text/javascript">
		_error_cannot_send_to_friend = "{t _error_cannot_send_to_friend}";
	</script>

{% endif %}
