{% if !empty(products) %}
	<div class="panel panel-danger compare" id="compareMenu">
		<div class="panel-heading">
			<span class="glyphicon glyphicon-eye-close"></span>
			{t _compared_products}
		</div>

		<div class="content">
			<ul class="list-unstyled">
			{% for product in products %}
				[[ partial("compare/block/item.tpl") ]]
			{% endfor %}
			</ul>

			<div class="compareBoxMenu">
				<a class="btn btn-default btn-small" href="{link compare/index returnPath=true query="return=return"}">{t _view_comparison}</a>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		new Compare.Menu(('compareMenu'));
	</script>
{% endif %}
<div id="compareMenuContainer"></div>