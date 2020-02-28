<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>Midollar</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<link href="style.css" rel="stylesheet" type="text/css" />

<link rel="stylesheet" type="text/css" href="coin-slider.css" />

<script type="text/javascript" src="js/cufon-yui.js"></script>

<script type="text/javascript" src="js/cufon-libsans-r-b.js"></script>

<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>

<script type="text/javascript" src="js/script.js"></script>

<script type="text/javascript" src="js/coin-slider.min.js"></script>



<!-- CuFon ends -->

{literal}

<script type="text/javascript">



  var _gaq = _gaq || [];

  _gaq.push(['_setAccount', 'UA-33683425-1']);

  _gaq.push(['_trackPageview']);



  (function() {

    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;

    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';

    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);

  })();



</script>

{/literal}

</head>

<body>

<div class="main">



  <div class="header">

    <div class="header_resize">

      <div class="logo">

        <a href="?a=home"><img src="images/logo.gif" width="321" height="75" border="0"></a>

      </div>

      <div class="social">

      </div>

      <div class="clr"></div>

      

      <div class="menu_nav">

          <ul>

            <li {if !$frm.a || $frm.a == 'home'}class="active"{/if}><A href="?a=home"><span>Home Page</span></a></li>

            {if $userinfo.logged != 1}

                <li {if $frm.a == 'signup'}class="active"{/if}><a href="?a=signup"><span>Sign Up</span></a></li>

      			<li {if $frm.a == 'login'}class="active"{/if}><a href="?a=login"><span>Login</span></a></li>

      		  {/if}

               <li {if $frm.a == 'rules'}class="active"{/if}><a href="?a=rules"><span>Terms</span></a></li>

            	 <li {if $frm.a == 'faq'}class="active"{/if}><a href="?a=faq"><span> F.A.Q</span></a></li>

      			   <li {if $frm.page == 'aboutus'}class="active"{/if}><a href="?a=cust&page=aboutus"><span>About Us</span></a></li>

            	 <li {if $frm.page == 'howtoinvest'}class="active"{/if}><a href="?a=cust&page=howtoinvest"><span>How to Invest</span></a></li>

            	 <li {if $frm.a == 'support'}class="active"{/if}><a href="?a=support"><span>Contacts</span></a></li>

            	 {if $userinfo.logged == 1}

            	 <li {if $frm.a == 'logout'}class="active"{/if}><a href="?a=logout"><span>LogOut</span></a></li>

            {/if}

              

          </ul>

      </div>

      <div class="clr"></div>

      <div class="slider">

        <div id="coin-slider">

          <img src="images/slide1.jpg" width="940" height="369" alt="slide1">

          <img src="images/slide2.jpg" width="940" height="369" alt="slide2">

        </div>

        <div class="clr"></div>

      </div>

    </div>

  </div>