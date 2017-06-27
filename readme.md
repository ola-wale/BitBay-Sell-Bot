<h1>BitBay-Sell-Bot <b>for PHP CLI</b></h1>
<b>How It Works</b> <br />
Reduces the lowest order on the order book by -0.01 and places a new order for this price (See line 59: <i>$sell_price = $highest_price-0.01;</i>)<br />
<br />
<br />
<h1>How to use</h1>
<br />
php highest.php --minsellprice=8000 --mindiff=-10
<br />
<h1>Flags</h1>

--minsellprice (required): Lowest Price to sell for <i>BitBay-Sell-Bot won't place an order for a price below this</i><br />
--mindiff (optional,default:-10): Difference Between the lowest order on the order book and ours - <i>To prevent people who think they're clever from tricking our bot into selling low</i><br /> :p

PS: Latency Might affect trades