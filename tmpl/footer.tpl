      </div>
{literal}
<script language=javascript>
function checklogin() {
  if (document.loginform.username.value=='') {
    alert("Please enter your username!");
    document.loginform.username.focus();
    return false;
  }
  if (document.loginform.password.value=='') {
    alert("Please enter your password!");
    document.loginform.password.focus();
    return false;
  }
  return true;
}
</script>
{/literal}

<div class="sidebar">
	{if $userinfo.logged != 1}
	<div class="logbox">
		<div class="gadget">
			<DIV class="login">

				<form method=post name=loginform onsubmit="return checklogin()">
					<input type=hidden name=a value='do_login'>
					<input type=hidden name=follow value=''>
					<table width="90%" border=0 align="center" cellpadding=2 cellspacing=0>
						<tr>
							<th colspan=2>
								<img src=images/q.gif width=1 height=70></th>
						</tr>

						<tr>
							<td width="20">&nbsp;</td>
							<td>Username:</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<input type=text name=username class=inpts size=23></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>Password:</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<input type=password name=password class=inpts size=23></td>
						</tr>
						<tr>
							<td class=menutxt>&nbsp;</td>
							<td>
								<input type=submit value="Login" class=sbmt size=15></td>
						</tr>
						<tr>
							<td colspan=2 align=right>
								<a href=?a=forgot_password class=menutxt>Password recovery</a>
							</table>
						</form>
					</DIV>
				</div>
			</div>
			<div class="gadget">
				<h2 class="star">
				<br>
				<div id="infobox">
					<DIV id="info">
						<table>
							<tr><td colspan=2>Referral program &nbsp</td></tr>
							<tr><td>&nbsp</td><td><img src="images/ref.png"></td></tr>
						</table>
					</DIV>
					<br><br><br><br>

				</div>
                <br>
                <br>
                <a href='https://www.goldpoll.com/details/26733/' target='_blank'><img src='https://www.goldpoll.com/locator/image.php?hId=26733' border='0' alt='Midollar HYIP Details on GoldPoll'></a>
			</div>
			{else}
			<div class="gadget">
        	 <h2 class="star">Account Navigation</h2>
        	 <div class="clr"></div>
        	 <ul class="sb_menu">
        	 <li><a href="?a=account">Account Summary</a></li>
        	 <li><a href="?a=deposit">Make Deposit</a></li>
			           <li><a href="?a=deposit_list">Your Deposits Statistics</a></li>
			 <li><a href="?a=deposit_history">Deposits History</a></li>
			 <li><a href="?a=earnings">Earnings History</a></li>
			 <li><a href="?a=earnings&amp;type=commissions">Referrals History</a></li>
			 <li><a href="?a=withdraw">Withdraw</a></li>
			 <li><a href="?a=withdraw_history">Withdrawals History</a></li>
			 		  <li><a href="?a=referals">Your Referrals</a></li>
			 <li><a href="?a=referallinks">Referral Links</a></li>
			 		  <li><a href="?a=edit_account">Edit Personal Account</a></li>
			 		  <li><a href="?a=logout">Logout</a></li>
        	 </ul>
        	</div>

			{/if}
		</div>

      <div class="clr"></div>
    </div>
  </div>

  <div class="fbg">
    <div class="fbg_resize">
      <div class="col">
<span>International Investment Group </span><a href="#">Midollar</a> &copy; Copyright 2013. All rights reserved.
        </div>
      <div class="clr"></div>
    </div>
  </div>
  <div class="footer">
    <div class="footer_resize">
  <p class="lf">
  	<a href="?a=home">Home Page</a>
  	| {if $userinfo.logged != 1}
  	<a href="?a=signup">Sign Up</a>
  	|
  	<a href="?a=login">Login</a>
  	|  {/if}
  	<a href="?a=rules">Terms</a>
  	|
  	<a href="?a=faq">F.A.Q</a>
  	|
  	<a href="?a=cust&page=aboutus">About Us</a>
  	|
  	<a href="?a=cust&page=howtoinvest">How to Invest</a>
  	|
  	<a href="?a=support">Contacts</a>
  	{if $userinfo.logged == 1} |
  	<a href="?a=logout">Logout</a>
  	{/if}
  </p>
      <div class="clr"></div>
    </div>
  </div>
</div>
</body>
</html>