<fieldset class="orderShipment_controls error" {if $notShippable}style="display: none;"{/if}>
   <fieldset class="orderShipment_status error">
       <label>{t _status}: </label>
       <select name="status" id="orderShipment_status_{$shipment.ID}" {denied role='order.update'}disabled="disabled"{/denied}">
           {foreach key="statusID" item="status" from=$statuses}
               <option value="{$statusID}" id="orderShipment_status_{$shipment.ID}_{$statusID}" {if $shipment.status == $statusID}selected{/if}>{$status}</option>
           {/foreach}
       </select>
       {img style="display: none" id="orderShipment_status_`$shipment.ID`_feedback" src="image/indicator.gif"}
   </fieldset>
</fieldset >