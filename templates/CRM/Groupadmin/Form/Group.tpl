{foreach from=$groups key=k item=group}
{assign var="elementname" value="staff_id_$k"}
<fieldset>
<h4>Group: {$group}</h4>
<div>
  <span>{$form.$elementname.label}</span>
  <span>{$form.$elementname.html}</span>
</div>
</fieldset>
<br/>
{/foreach}

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
