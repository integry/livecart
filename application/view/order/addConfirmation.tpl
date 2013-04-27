<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{t _item_added_title}</h4>
			</div>
			<div class="modal-body">
				{include file="order/changeMessages.tpl"}

				{if $error}
					<div class="errorMessage">{$error}</div>
				{/if}

				<p class="addedToCart">{$msg}</p>
			</div>
			<div class="modal-footer">
				{include file="order/block/navigationButtons.tpl" hideTos=true}
			</div>
		</div>
	</div>
</div>