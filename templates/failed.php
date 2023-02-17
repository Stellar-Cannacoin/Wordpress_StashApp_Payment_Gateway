<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Payment Failed - StashApp</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;900&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
		<style>
			body {
				font-family: 'Roboto', sans-serif;
				margin: 0;
				padding: 0;
				background-color: #f2f2f2;
			}
			.paymentAppWrapper {
				height: 100%;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: space-between;
			}
			.navWrapper {
				padding-left: 30px;
				display: flex;
				flex-direction: row;
				align-items: center;
				justify-content: flex-end;
				width: 100%;
				height: 70px;
				background-color: #fff;
				box-shadow: 0px 2px 2px #ccc;
			}
			.footerWrapper {
				width: 100%;
				height: 70px;
				display: flex;
				align-items: center;
				justify-content: center;
			}
			.dataWrapper {
				display: flex;
				justify-content: center;
				align-items: center;
				padding: 20px;
				background-color: #fff;
				border-radius: 20px;
				box-shadow: 2px 2px 8px #ccc;
				height: 200px;
				width: 200px;
			}
			a {
				text-decoration: none;
				font-weight: 900;
				color: #000;
			}
			
		</style>
		</head>
	
	
		<body>
			<div class="paymentAppWrapper">
				<div class="navWrapper">
					<a href="https://stashapp.cloud" target="_blank" style="margin-right: 20px;">
						<img src="https://stashapp.cloud/wp-content/uploads/2022/09/new_logo.png" style="height: 50px; border-radius: 10px;"/>
					</a>
					<a href="https://play.google.com/apps/testing/org.stellar.cannacoin.stashapp.wallet" target="_blank" style="margin-right: 20px;">
						<img src="https://stellarcannacoin.org/wp-content/uploads/2022/08/googlestore-logo.png" style="height: 50px;"/>
					</a>
					<a href="https://testflight.apple.com/join/cv3UCvFd" target="_blank" style="margin-right: 20px;">
						<img src="https://stellarcannacoin.org/wp-content/uploads/2022/08/appstore-logo.png"  style="height: 50px;"/>
					</a>
				</div>
				<div id="verifyingPayment" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
					<h1 style="margin-bottom: 0px;;">Payment failed</h1>
					<h3 style="margin-top: 0px;">The transaction was declined</h3>

					<div id="t1" class="dataWrapper" style="margin-top: 20px;">
						<svg style="height: 100px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Pro 6.1.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M310.6 361.4c12.5 12.5 12.5 32.75 0 45.25C304.4 412.9 296.2 416 288 416s-16.38-3.125-22.62-9.375L160 301.3L54.63 406.6C48.38 412.9 40.19 416 32 416S15.63 412.9 9.375 406.6c-12.5-12.5-12.5-32.75 0-45.25l105.4-105.4L9.375 150.6c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L160 210.8l105.4-105.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-105.4 105.4L310.6 361.4z"/></svg>
					</div>
					<div style="margin-top: 40px">
						<a href="<?php echo get_home_url(); ?>">Return to webshop</a>
					</div>
					
				</div>
				<div id="completedPayment" style="width: 100%; display: flex; flex-direction: column; align-items: center; display: none;">
					<h1>Redirecting...</h1>
				</div>
				<div id="failedPayment" style="width: 100%; display: flex; flex-direction: column; align-items: center; display: none;">
					<h1>Redirecting...</h1>
				</div>
				<div class="footerWrapper">
					<p>
                        Developed by <a href="https://stashapp.cloud">StashApp</a> & <a href="https://stellarcannacoin.org">Stellar Cannacoin</a>
					</p>
				</div>
			</div>
	</body>
</html>