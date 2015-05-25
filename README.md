# php-api
PHP class for [LEOBIT](https://www.leobit.net) API

##methods
``` 
placeOrder($type, $amount, $price, $currency = 'btc')
cancelOrder($orderId) 
getBalance()
cryptoWithdraw($address, $amount, $currency = 'btc')
getOrderbook($type = 'both', $curreny = 'btc')
getOpenOrders($curreny = 'btc')
``` 

##examples

###public methods
```
include 'Leobit.php';

$leobitApi = new Leobit();
$orderbook = $leobitApi->getOrderbook('both', 'btc');
``` 
if you want to use same instance for private methods you have to authenticate
```
$leobitApi->SetCredentials('myKey','mySecret','example@email');
$orderResult = $leobitApi->placeOrder('sell', '100', '0.001', 'btc');
```
###private methods
```
include 'Leobit.php';

$leobitApi = new Leobit('myKey','mySecret','example@email');
$balance = $leobitApi->getBalance();
$orderResult = $leobitApi->placeOrder('sell', '100', '0.001', 'btc');
``` 
