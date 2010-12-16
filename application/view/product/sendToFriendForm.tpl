{if 'ENABLE_PRODUCT_SHARING'|config
	&&
	($user.ID || 'ENABLE_ANONYMOUS_PRODUCT_SHARING'|config)
}

	<div id="sharingSection" class="productSection sharingSection">
		<h2>{maketext text="_share_product_name" params=$product.name_lang}</h2>

		<div id="sendToFriendRepsonse"></div>

		<div id="shareProduct">
			{form action="controller=product action=sendToFriend id=`$product.ID`" handle=$sharingForm method="POST"
				onsubmit="new Product.Share(this); return false;" }
				<div class="producSharingForm">

					<p class="required">
						{err for="friendemail"}
							<label class="wide" for="friendemail">{t _friend_email}:</label>
							{textfield class="text wide"}
						{/err}
					</p>
					{if !$user.ID}
						<p class="required"> 
							{err for="nickname"}
								<label class="wide" for="nickname">{t _nickname}:</label>
								{textfield class="text wide"}
							{/err}
						</p>
					{/if}
					
					<p>
						{err for="notes"}
							<label class="wide">{t _notes}:</label>
							{textarea class="text"}
						{/err}
					</p>
				</div>

				<p>
					<input class="submit" type="submit" value="{tn _send_to_friend}" /> <span class="pi" style="display: none;"></span>
				</p>

			{/form}
		</div>
	</div>
	<script type="text/javascript">
		_error_cannot_send_to_friend = "{t _error_cannot_send_to_friend}";
	</script>
	
{/if}