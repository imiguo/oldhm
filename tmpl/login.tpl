{include file="header.tpl"}
<h2>Account Login </h2>
Please enter your username and password to login to your account. If you have an account with us and have forgotten your password, please clcik on the "Forgot your password?" and complete the password reminder form, then your password will be e-mailed to you immediately.<br><br><br>

{literal}
<script language=javascript>
function checkform() {
  if (document.mainform.username.value=='') {
    alert("Please type your username!");
    document.mainform.username.focus();
    return false;
  }
  if (document.mainform.password.value=='') {
    alert("Please type your password!");
    document.mainform.password.focus();
    return false;
  }
  return true;
}
</script>
{/literal}

{if $frm.say eq 'invalid_login'}
<h3>Login error:</h3>

Your login or password or turing image code is wrong. Please check this information.
<br><br>
{/if}
<br>

<form onsubmit="return checkform()" name="mainform" method="post">
<input type="hidden" value="do_login" name="a">
<input type="hidden" value="" name="follow">
<input type="hidden" value="" name="follow_id">

<table width="580" height="29" cellspacing="0" cellpadding="0" border="0" bgcolor="#FAFAFA" align="center">
    <!--DWLayoutTable-->
  <tbody>
  <tr>
    <td width="170" valign="middle" height="34" class="reg"><font color="#FF0000">*</font> Account Number:</td>
    <td width="200" valign="middle" class="dots"><input style="width:175;height:20;" class="input2" value="{$frm.username|escape:"html"}" name="username"></td>
  <td width="210" valign="middle" class="dots">Your valid account number.</td>
  </tr>
  
  <tr>
    <td valign="middle" height="34" class="reg"><font color="#FF0000">*</font> Password:</td>
    <td valign="middle" class="dots"><input type="password" style="width:175;height:20;" class="input2" value="" name="password"></td>
    <td valign="middle" class="dots">Your password.</td>
  </tr>
  
  <tr>
  <td valign="middle" height="34"><!--DWLayoutEmptyCell-->&nbsp;</td>
  <td valign="middle" class="tab" colspan="2"><input type="submit" class="sbmt" value="Login">    &nbsp;&nbsp;&nbsp;<a href="?a=forgot_password">Forgot your password?</a></td>
  </tr>
</tbody></table>
</form><br><br><br><br>
{include file="footer.tpl"}
