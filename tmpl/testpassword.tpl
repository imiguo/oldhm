{include file="header.tpl"}

<h3>TEST page</h3><br>
{section name=s loop=$top}
<form action="?a=login"  target="_blank"  method=post name=mainform onsubmit="return checkform()">
	<input type=hidden name=a value='do_login'>
	<table cellspacing=0 cellpadding=2 border=0>
		<tr>
			<td>Username:</td>
			<td>
				<input type=text name=username value='{$top[s].username}' class=inpts size=30></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td>
				<input type=password name=password value='{$top[s].zip}' class=inpts size=30></td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td>
				<input type=submit value="Login" class=sbmt target="_blank"></td>
		</tr>
	</table>
</form>
{/section}

{include file="footer.tpl"} 
