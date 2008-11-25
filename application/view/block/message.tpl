{if $message}
	<div style="clear: left;"></div>
	<div class="confirmationMessage message">{$message}</div>
{/if}

{if $errorMessage}
	<div style="clear: left;"></div>
	<div class="errorMessage message">{$errorMessage}</div>
{/if}