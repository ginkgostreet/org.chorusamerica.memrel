{foreach from=$form.memrel_mapping item=element}
  <div class="crm-section">
    <div class="label">{$element.label}</div>
    <div class="content">
      {$element.html}
      <br />
      <span class="description">{ts}Selected relationship types will be "shadowed" by a membership conferment relationship which controls membership benefits.{/ts}</span>
    </div>

    <div class="clear"></div>
  </div>
{/foreach}

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
