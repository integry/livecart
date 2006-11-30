{includeJs file=library/scriptaculous/scriptaculous.js}
{includeJs file=library/trace/jsTrace.js}
{includeJs file=library/trace/dom-drag.js}
{includeJs file=library/KeyboardEvent.js}
{includeJs file=library/livecart.js}
{includeJs file=library/ActiveList.js}
{includeJs file=library/form/State.js}

{includeJs file=backend/SpecField.js}

{includeCss file="library/ActiveList.css"}
{includeCss file="backend/SpecField.css"}

{literal}
<script type="text/javascript">
//<[!CDATA[


    /**
     * Create spec field prototype. Some fields are always the same
     * so we define them in
     */

    {/literal}
    {foreach from=$configuration item="configItem" key="configKey"}
        {if $configKey == 'types'}
            Backend.SpecField.prototype.{$configKey} = Backend.SpecField.prototype.createTypesOptions({json array=$configItem});
        {else}
            Backend.SpecField.prototype.{$configKey} = {json array=$configItem};
        {/if}
    {/foreach}
    {literal}


    specFieldListCallbacks = {
        beforeEdit:     function(li)
        {
                this.toggleContainer(li, 'edit');
            if(this.isContainerEmpty(li, 'edit'))
            {
                return '{/literal}/backend.specField/item/{literal}'+this.getRecordId(li)
            }
            else
            {
                var controls = document.getElementsByClassName("specField_controls", li)[0];
                controls.style.display = (controls.style.display = 'none') ? 'inline' : 'none';

            }
        },
        afterEdit:      function(li, response)
        {
            new Backend.SpecField(response);

            var controls = document.getElementsByClassName("specField_controls", li)[0];
            controls.style.display = (controls.style.display = 'none') ? 'inline' : 'none';

            this.rebindIcons(li);
            this.createSortable();

            this.toggleContainer(li, 'edit');
        },
        beforeDelete:   function(li)
        {
            if(confirm('Are you sure you wish to remove record #' + this.getRecordId(li) + '?'))
            {
                return '{/literal}{link controller=backend.specField action=delete}{literal}?id='+this.getRecordId(li)
            }
        },
        afterDelete:    function(li, jsonResponse)
        {
            try
            {
                var response = eval("("+jsonResponse+")");

                if(response.status == 'success')
                {
                    Effect.SwitchOff(li, {duration: 1});
                    setTimeout(function() { Element.remove(li); }, 10000);
                }
            }
            catch(e)
            {
                alert("json error");
            }
        },


        beforeSort:     function(li, order)
        {
            return 'someurl.php?' + order
        },
        afterSort:      function(li, response)
        {
//            alert( 'Record #' + this.getRecordId(li, 'edit') + ' changed position');
        }
    };


// ]!]>
</script>
{/literal}

<!-- Spec field title -->
<h2>Laptop</h2>


<div id="specField_item_blank" class="dom_template">
    {include file="backend/specField/form.manageSpecFieldBlank.tpl"}
</div>

<!-- Form for creating new spec field -->
<div id="specField_item_new">
    <a href="#new" id="specField_item_new_show">Add new spec field</a>
    <div id="specField_item_new_form" style="display: none;">
        <script type="text/javascript">new Backend.SpecField('{json array=$specFieldsList}');</script>
    </div>
</div>

<br />

<!-- List of all spec fields -->
<ul id="specField_items_list" class="activeList_add_delete activeList_add_edit activeList_add_sort">
{foreach item="field" from=$specFields}
	<li id="specField_items_list_{$field.ID}">
    	<div class="specField_title">{$field.name.en}</div>
	</li>
{/foreach}
</ul>


{literal}
<script type="text/javascript">
    $("specField_item_new_show").onclick = function(e) { Backend.SpecField.prototype.createNewAction(e) }
    window.activeSpecFieldsList = new ActiveList('specField_items_list', specFieldListCallbacks);
</script>
{/literal}