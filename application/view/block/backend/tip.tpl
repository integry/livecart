<div class="tip">
	<div>{$tipContent}</div>
	{capture name="EfectFade"}{literal}new Effect.Fade(this.parentNode, {duration: 0.3});{/literal}{/capture}
	{img src="image/backend/icon/cancel.png" onclick=$smarty.capture.EfectFade}
</div>