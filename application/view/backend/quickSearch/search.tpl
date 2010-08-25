<ul>
	{if $result.Product.count > 0}
		<li>
			<h3>{t _products}</h3>
			<span>({$result.Product.count})</span>
		</li>
		{foreach $result.Product.records as $record}
			<li>
				<a href="{link controller=backend.category query="rt=`$randomToken`"}#product_{$record.ID}__">{$record.name_lang|escape}</a>
				{* or frontend link: <a href="{productUrl product=$record}">{$record.name_lang|escape}</a> *}
				<span>({t _sku}: {$record.sku|escape})</span>
			</li>
		{/foreach}
	{/if}

	{if $result.Category.count > 0}
		<li>
			<h3>{t _categories}</h3>
			<span>({$result.Category.count})</span>
		</li>
		{foreach $result.Category.records as $record}
			<li>
				<a href="{link controller=backend.category query="rt=`$randomToken`"}#cat_{$record.ID}#tabProducts__">{$record.name_lang|escape}</a>
				{* or frontend link: <a href="{categoryUrl data=$record}">{$record.name_lang|escape}</a> *}
				<span></span>
			</li>
		{/foreach}
	{/if}

	{if $result.User.count > 0}
		<li>
			<h3>{t _users}</h3>
			<span>({$result.User.count})</span>
		</li>
		{foreach $result.User.records as $record}
			<li>
				<a href="{link controller=backend.userGroup query="rt=`$randomToken`"}#user_{$record.ID}__">{$record.firstName|escape} {$record.lastName|escape}</a>
				<span>({$record.email|escape})</span>
			</li>
		{/foreach}
	{/if}

	{if $result.CustomerOrder.count > 0}
		<li>
			<h3>{t _orders}</h3>
			<span>({$result.CustomerOrder.count})</span>
		</li>
		{foreach $result.CustomerOrder.records as $record}
			<li>
				<a href="{link controller=backend.customerOrder query="rt=`$randomToken`"}#order_{$record.ID}__">{$record.invoiceNumber|escape}</a>
				<span>({$record.formattedTotalAmount})</span>
			</li>
		{/foreach}
	{/if}

	{if $result.Manufacturer.count > 0}
		<li>
			<h3>{t _manufacturers}</h3>
			<span>({$result.Manufacturer.count})</span>
		</li>
		{foreach $result.Manufacturer.records as $record}
			<li>
				<a href="{link controller=backend.manufacturer query="rt=`$randomToken`"}#manufacturer_{$record.ID}__">{$record.name|escape}</a>
				<span></span>
			</li>
		{/foreach}
	{/if}

	{if $result.NewsPost.count > 0}
		<li>
			<h3>{t _news_posts}</h3>
			<span>({$result.NewsPost.count})</span>
		</li>
		{foreach $result.NewsPost.records as $record}
			<li>
				<a href="{link controller=backend.siteNews query="rt=`$randomToken`"}#news_{$record.ID}">{$record.title|escape}</a>
				<span></span>
			</li>
		{/foreach}
	{/if}
	
	
	{if $result.StaticPage.count > 0}
		<li>
			<h3>{t _static_pages}</h3>
			<span>({$result.StaticPage.count})</span>
		</li>
		{foreach $result.StaticPage.records as $record}
			<li>
				<a href="{link controller=backend.staticPage query="rt=`$randomToken`"}#page_{$record.ID}">{$record.title|escape}</a>
				<span></span>
			</li>
		{/foreach}
	{/if}

	{if $result.DiscountCondition.count > 0}
		<li>
			<h3>{t _static_pages}</h3>
			<span>({$result.DiscountCondition.count})</span>
		</li>
		{foreach $result.DiscountCondition.records as $record}
			<li>
				<a href="{link controller=backend.discount query="rt=`$randomToken`"}#discount_{$record.ID}__">{$record.name|escape}</a>
				<span></span>
			</li>
		{/foreach}
	{/if}

	{* notices about not found types *}
	{if $result.Product.count <= 0}
		<li class="notfound">{t _product_not_found}</li>
	{/if}
	{if $result.Category.count <= 0}
		<li>{t _category_not_found}</li>
	{/if}
	{if $result.User.count <= 0}
		<li>{t _user_not_found}</li>
	{/if}
	{if $result.CustomerOrder.count <= 0}
		<li>{t _order_not_found}</li>
	{/if}
	{if $result.Manufacturer.count <= 0}
		<li>{t _manufacturer_not_found}</li>
	{/if}
	{if $result.NewsPost.count <= 0}
		<li>{t _news_post_not_found}</li>
	{/if}
	{if $result.StaticPage.count <= 0}
		<li>{t _static_page_not_found}</li>
	{/if}

	{if $result.DiscountCondition.count <= 0}
		<li>{t _business_rule_not_found}</li>
	{/if}
</ul>