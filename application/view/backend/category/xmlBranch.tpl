<tree id="{$rootID}">
    {foreach item="category" from=$categoryList}
    <item child="{$category.childrenCount}" id="{$category.ID}" text="{$category.name}"></item>
    {/foreach}
</tree>