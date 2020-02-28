{include file="header.tpl"}

<h2>Midollar.com Support</h2>
If you are already a Registered Investor of Midollar.com please login into your account and click again on "Support". For security purpose, we will not reply to any qyestion concerning an investment, if you are not logged in.
If you are not an Investor yet, please send your question by the form below.<br><br>

<font color="red">Monitor admins:</font> Please do NOT send spam emails. We do NOT respond to the emails we are not interested in the offers. <br><br>

{if $say eq 'send'}
Message has been successfully sent. We will back to you in next 24 hours. Thank you.<br><br>
{else}
<script language=javascript>
{if $userinfo.logged == 1}
{literal}
function checkform() { 
  if (document.mainform.message.value == '') {
    alert("Please type your message!");
    document.mainform.message.focus();
    return false;
  }
  return true;
}
{/literal}
{else}
{literal}
function checkform() {
  if (document.mainform.name.value == '') {
    alert("Please type your full name!");
    document.mainform.name.focus();
    return false;
  }
  if (document.mainform.email.value == '') {
    alert("Please enter your e-mail address!");
    document.mainform.email.focus();
    return false;
  }
  if (document.mainform.message.value == '') {
    alert("Please type your message!");
    document.mainform.message.focus();
    return false;
  }
  return true;
}
{/literal}
{/if}
</script>

<h4>Support Form</h4>
<form onsubmit="return checkform()" name="mainform" method="post">
<input type="hidden" value="support" name="a">
<input type="hidden" value="send" name="action">

<table width="580" cellspacing="0" cellpadding="2" border="0" bgcolor="#FAFAFA" align="center">
  <!--DWLayoutTable-->
<tbody>
<tr>
<td width="270" valign="middle" height="34" class="reg">Name<font color="#FF0000">*</font>:</td>
{if $userinfo.logged}
<td width="114"><b>{$userinfo.name}</b></td>
<input type=hidden name=name value={$userinfo.name}>
{else}
 <td width="310" valign="middle" class="dots"><input type="text" style="width:175;height:20;" class="input2" name="name"></td>
 {/if}
</tr>
<tr>
<td valign="middle" height="34" class="reg">Email<font color="#FF0000">*</font>:</td>
{if $userinfo.logged}
<td><b>{$userinfo.email}</b></td>
<input type=hidden name="email" value={$userinfo.email}>
{else}
 <td class="dots"><input type="text" style="width:175;height:20;" class="input2" name="email"></td>
 {/if}
</tr>

<tr>
<td valign="middle" height="34" class="reg">Subject<font color="#FF0000">*</font>:</td>
<td class="dots"><input type="text" style="width:175;height:20;" class="input2"></td>
</tr>

<tr>
<td valign="middle" height="30" align="left" class="reg">Message<font color="#FF0000">*</font>:</td>
<td valign="middle" class="reg" colspan="2"><div style="padding-top:5px;padding-bottom:5px;"><textarea style="width:300px;" rows="10" cols="30" class="input2" name="message"></textarea></div></td>
</tr>

<tr>
 <td valign="top" height="45"><!--DWLayoutEmptyCell-->&nbsp;</td>
 <input type="hidden" name="support_token" value="{$token}">
 <td valign="middle" align="left" colspan="2"><input type="submit" class="sbmt" value="Submit"></td>
</tr>
</tbody></table>
</form>
{/if}

{include file="footer.tpl"}
