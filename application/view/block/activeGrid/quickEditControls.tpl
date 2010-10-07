<div class="clear"></div>
<div class="controls">
	{if !$nosave}
	<span class="progressIndicator" style="display: none;"></span>
	<input type="submit" name="save" class="submit" value="{t _save}" onclick="return ActiveGrid.QuickEdit.onSubmit(this);">
	{t _or}
	{/if}
	<a class="cancel" href="javascript:void(0);" onclick="return ActiveGrid.QuickEdit.onCancel(this);">{t _cancel}</a>
</div>
