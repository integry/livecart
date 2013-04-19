{loadJs}
{if $products}
	<div class="panel compare" id="compareMenu">
		<div class="panel-heading">{t _compared_products}</div>

		<div class="content">
			<ul class="list-unstyled">
			{foreach from=$products item=product}
				{include file="compare/block/item.tpl"}
			{/foreach}
			</ul>

			<div class="compareBoxMenu">
				<a href="{link compare/index returnPath=true query="return=$return"}">{t _view_comparison}</a>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		new Compare.Menu($('compareMenu'));
	</script>
{/if}
<div id="compareMenuContainer"></div>