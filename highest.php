<?php

	define('BITBAY_KEY','');
	define('BITBAY_SECRET','');

	///////////////
	//BITBAY CODE//
	///////////////
	function BitBay_Trading_Api($method, $params = array())
	{
		sleep(1); //wait to prevent hitting BB's api limits.
		$key = BITBAY_KEY;
		$secret = BITBAY_SECRET;

		$params["method"] = $method;
		$params["moment"] = time();

		$post = http_build_query($params, "", "&");
		$sign = hash_hmac("sha512", $post, $secret);
		$headers = array(
        "API-Key: " . $key,
        "API-Hash: " . $sign,
		);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, "https://bitbay.net/API/Trading/tradingApi.php");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$ret = json_decode(curl_exec($curl));
		if(isset($ret->code) && $ret->code != 200){
			if(isset($ret->message) && $ret->message){
				die("\n$ret->message");
			} else {
				die("\nan unknown error has occurred.");
			}
		}
		return $ret;
	}

	$options = getopt('',array("minsellprice:","mindiff:"));
	if(!isset($options['minsellprice']) || !$options['minsellprice']){
		die("Please specify a minimum sell price using the --minsellprice flag. ex : --minsellprice=4232");
	}
	$min_sell_price = $options['minsellprice']; //minimum amount to sell for
	$min_difference = -10; // minimum difference to adjust;
	if(isset($options['mindiff']) && $options['mindiff']){
		$min_difference = $options['mindiff'];
	}
	echo "
   _____ ________    __    _____   ________   ____ ____________   __   __   __   __   __   __   __
  / ___// ____/ /   / /   /  _/ | / / ____/  / __ )_  __/ ____/  / /  / /  / /  / /  / /  / /  / /
  \__ \/ __/ / /   / /    / //  |/ / / __   / __  |/ / / /      / /  / /  / /  / /  / /  / /  / /
 ___/ / /___/ /___/ /____/ // /|  / /_/ /  / /_/ // / / /___   /_/  /_/  /_/  /_/  /_/  /_/  /_/
/____/_____/_____/_____/___/_/ |_/\____/  /_____//_/  \____/  (_)  (_)  (_)  (_)  (_)  (_)  (_)
                                                                                                  ";
	echo "\nSELLING BTC FROM {$min_sell_price}PLN and {$min_difference}PLN minimum price difference PLN TYPE OKAY TO CONFIRM\n";
	$confirmation = trim(fgets(STDIN));

	if("OKAY" === $confirmation){
		//continue
		echo "\nmoving on!";
		while(1){
			try{
				$order_cancelled = 0;
				$orders = json_decode(file_get_contents("https://bitbay.net/API/Public/BTCPLN/orderbook.json"));
				$asks = $orders->asks;
				$highest = $asks[0]; //the highest ask
				$highest_price = $highest[0]; //the highest ask price
				$sell_price = $highest_price-0.01;
				$my_offers = BitBay_Trading_Api('orders',array('limit'=>3));
				$active_orders = array();
				//sort active orders into an array
				if(!$my_offers){
					echo "\nno active orders";
					continue;
				}
				$counter = 0;
				foreach($my_offers as $my_offer){
					if($my_offer->status == 'active' && $my_offer->payment_currency == 'PLN'){
						$active_orders[$counter] = $my_offer;
						$active_orders[$counter]->real_price = $my_offer->start_price / $my_offer->start_units;
						$counter++;
					}
				}
				//go through all orders - if the order price is lower than the active bid on the exchange -- cancel them
				if($active_orders){
					foreach($active_orders as $active_order){
						$current_price = $active_order->real_price;
						$current_id = $active_order->order_id;
						if($highest_price < $current_price){
						if($sell_price >= $min_sell_price){
							echo "\n CANCELLING ORDERS";
							$res = BitBay_Trading_Api('cancel',array('id'=>$current_id));
							$order_cancelled = 1;
						}  else {
						echo "\n NEW BID LOWER THAN MIN BID ;(";
						}
							} else {
							echo "\n WE HAVE THE HIGHEST ORDER, NO NEED ;)";
						}
					}
				}
				$tosell = BitBay_Trading_Api('info',array('BTC'));
				$tosell = $tosell->balances->BTC->available;
				echo "\n BALANCE == $tosell";
				//$tosell = 0.12345; //amount of BTC to sell -- 0.1 for testing
				//place a new order
				if($order_cancelled){
					if($sell_price >= $min_sell_price){
						$difference = $highest_price - $sell_price;
						echo "\n DIFFERENCE is $difference";
						if($difference < $min_difference){
							echo "ABORTING -- DIFFERENCE TOO HIGH -- $difference\n";
							continue;
							} else {
							echo "\nPLACING NEW ORDER, SELLING 4BTC for $sell_price";
							if($sell_price >= $min_sell_price){
								$res = BitBay_Trading_Api('trade',array('type'=>'sell','currency'=>'BTC','amount'=>$tosell,'payment_currency'=>'PLN','rate'=>$sell_price));
								$res = $res->message;
								echo "\n SELL RESULT: $res";
							}
						}
						} else {
						echo "\n NEW BID LOWER THAN MIN BID ;(";
					}
				}
				} catch (Exception $e){
					//DN
			}
		}

		} else {
		//or die
		die();
	}