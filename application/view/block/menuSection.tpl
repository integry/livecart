{literal}
<link href="{/literal}{$menuCSS}{literal}" media="screen" rel="stylesheet" type="text/css"/>
{/literal}
{$menu_javascript}
{foreach from=$topList item=item}
 &nbsp; &nbsp; <a href="{link controller=$item.controller action=$item.action}">{translate text=$item.title}</a>
{/foreach}