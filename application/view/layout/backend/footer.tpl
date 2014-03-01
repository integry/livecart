
	<div class="clear"></div>
	</div>

	</div>
	<div id="pageFooter">
		<div style="float: left;">
			[[ config('POWERED_BY_BACKEND') ]]
		</div>
		{% if config('BACKEND_SHOW_FOOTER_LINKS') %}
			<div id="supportLinks" style="float: right; padding-left: 50px;">
				<a href="http://support.livecart.com" target="_blank">Customer Support</a>
				/
				<a href="http://forums.livecart.com" target="_blank">Forums</a>
			</div>
		{% endif %}
		<div id="footerStretch">
			&nbsp;
		</div>
	</div>
</div>


<script type="text/javascript">
	Backend.internalErrorMessage = '{t _internal_error_have_accurred}';
	new Backend.LayoutManager();
</script>

{% if !config('DISABLE_TOOLBAR') %}
	{block FOOTER_TOOLBAR}
{% endif %}

</body>
</html>


