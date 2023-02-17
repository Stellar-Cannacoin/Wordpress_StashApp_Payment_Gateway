<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Processing payment - StashApp</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;900&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
	
	
$(document).ready(async function() {
	var paymentId;
	const params = new URLSearchParams(window.location.search);
	if (params.has('transaction_id')) {
		paymentId = params.get('transaction_id');
		
		const timeoutId = setInterval(async function(){
			var val = await validatePayment();
			var jsonParse = JSON.parse(val);
			jsonParse.payload.tx_type = "payment_pos";
			$('#t2').html(JSON.stringify(jsonParse.payload));
			delete jsonParse.payload.items;
			console.log(jsonParse.payload)
			$("#t1").empty();
			generate(JSON.stringify(jsonParse.payload))

		}, 2000);
	} else {
		paymentId = "not found";
		console.error("[StashApp - Payments]: Missing transaction_id, something is not configured right")
	}
	console.log("PAYMENT"+paymentId)

	
	
	//alert("CAK: "+JSON.stringify(val))
	
	function generate(user_input) {
		//new QRCode(document.querySelector("#t1"),user_input)
		var qrcode = new QRCode(document.querySelector("#t1"), {
			text: user_input,
			width: 180, //default 128
			height: 180,
			colorDark : "#000000",
			colorLight : "#ffffff",
			correctLevel : QRCode.CorrectLevel.L
		});
	} 
	
	async function validatePayment() {
		return new Promise((resolve, reject) => {
			fetch("https://api.stashapp.cloud/api/v1/payment/process/"+paymentId).then(async (response) => {
					// handle the response
				//alert(JSON.stringify(response))
				console.log(response.status);
				console.log("PAYMENT"+response.data)
				let data = await response.text();
				var json = JSON.parse(data)
				console.log("data: "+JSON.stringify(json.payload.eurl))
				//window.location.href = json.payload.eurl;
				//alert(json.payload.eid)
				$("#cancelUrl").attr("href", "/checkout/payment/failed/?transaction_id="+json.payload.eid)
				// set cancel url
				if (json.payload.status == "completed") {
					window.location.href = json.payload.eurl;
					
					clearTimeout(timeoutId);
					$("#verifyingPayment").hide();
					$("#completedPayment").show();
					//window.location.replace(data.payload.eurl);
					
				} else if (json.payload.status == "cancelled") {
					window.location.href = "/checkout/payment/failed/?transaction_id="+json.payload.eid;

					clearTimeout(timeoutId);
					$("#verifyingPayment").hide();
					$("#failedPayment").show();
					//reject(data)	
				}
				resolve(data)
				/**else {
					//clearTimeout(timeoutId);
					reject("cancelled")
				}*/
				


				//alert(params)

			}).catch(error => {
					// handle the error
				reject(error)
				//clearTimeout(timeoutId);
				//alert(error)
			});
		})
	}
	
})




//if (params.has('page_id')) {
//	paymentId = params.get('page_id');
//}
//paymentId = params.get('page_id');

//alert(paymentId)


</script>
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
			@media only screen and (max-width: 600px) {
				#qr_tip {
					display: none;
				}
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
					<h1 style="margin-bottom: 0px;;">Waiting for payment</h1>
					<h3 style="margin-top: 0px;" id="qr_tip">Scan QR using StashApp to pay for your order</h3>

					<div id="t2" style="display: none;">sdf</div>
					<div id="t1" class="dataWrapper" style="margin-top: 20px;"><img height="150" src="https://retchhh.files.wordpress.com/2015/03/loading1.gif" /></div>
				</div>
				<div style="margin-top: 40px">
						<a href="#" id="cancelUrl">Cancel payment</a>
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