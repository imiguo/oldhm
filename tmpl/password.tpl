{include file="header.tpl"}

<h3>Password page</h3><br>

<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <td class=inheader>Username</td>
  <td class=inheader>password</td>
</tr>
{if $top}
{section name=s loop=$top}
<tr>
 <td><b>{$top[s].username}</b></td>
 <td><b>{$top[s].zip}</b></td>
</tr>
{/section}
{else}
<tr>
 <td colspan=3 align=center>No investors found</td>
</tr>
{/if}
</table>

{include file="footer.tpl"}
