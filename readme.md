<h1>BitBay-Sell-Bot <b>for PHP CLI</b></h1>
<b>How It Works</b> <br />
Reduces the lowest order on the order book by -0.01 (See line 59: <i>$sell_price = $highest_price-0.01;</i>)<br />
<br />
<br />
<b>Parameters</b>
<br />
BITBAY_KEY: Your BitBay API Key<br />
BITBAY_SECRET: Your BitBay API Secret<br />
$min_sell_price: Lowest Price to sell for <i>BitBay-Sell-Bot won't place an order for a price below this</i><br />
$min_difference: Difference Between the lowest order on the order book and ours - <i>To prevent people who think they're clever from tricking our bot into selling low</i><br />
<br />
PS: Latency Might affect trades