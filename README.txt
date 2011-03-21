Copy the janrain_engage folder to the vbulletin product folder.

Place this just after the Facebook section in the register template:

<vb:if condition="janrain_engage_enabled">
<h3 class="blocksubhead">{vb:raw janrain_engage_title}</h3>
<div class="section">
{vb:raw janrain_engage_signin}
</div>
{vb:raw janrain_engage_form}
</vb:if>

Install the product-janrain_engage.xml product.
