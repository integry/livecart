{assign var=enabledFeeds value='ENABLED_FEEDS'|config}
{assign var=storeName value='STORE_NAME'|config|escape}
{if array_key_exists('NEWS_POSTS', $enabledFeeds)}
	<link rel="alternate" type="application/rss+xml" title="[[storeName]] | {t _news_posts_feed}" href="{link controller=rss action=news}"/>
{/if}
{if array_key_exists('CATEGORY_PRODUCTS', $enabledFeeds) && !empty($category.ID)}
	<link rel="alternate" type="application/rss+xml" title="[[storeName]] | {t _category_products_feed} ({$category.name_lang|escape})" href="{link controller=rss action=products id=$category.ID}"/>
{/if}
{if array_key_exists('ALL_PRODUCTS', $enabledFeeds)}
	<link rel="alternate" type="application/rss+xml" title="[[storeName]] | {t _all_products_feed}" href="{link controller=rss action=products}"/>
{/if}

[[ partial("layout/frontend/header.tpl") ]]
{if !$hideLeft}
	[[ partial("layout/frontend/leftSide.tpl") ]]
{/if}
{* include file="layout/frontend/rightSide.tpl" *}
