{loadJs}
{if $products}
	<div class="box compare" id="compareMenu">
		<div class="title">
			<div>{t _compared_products}</div>
		</div>

		<div class="content">
			<ul>
			{foreach from=$products item=product}
				{include file="compare/block/item.tpl"}
			{/foreach}
			</ul>

			<div class="compareBoxMenu">
				<a href="{link compare/index returnPath=true}">{t _view_comparison}</a>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		new Compare.Menu($('compareMenu'));
	</script>
{/if}
<div id="compareMenuContainer"></div>