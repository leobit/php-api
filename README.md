# php-api
PHP class for leobit.net API

##dependecies 
[jQuery](https://jquery.com/)
[pusher](https://pusher.com/)


##example
```
include 'Leobit.php';

$leobitApi = new Leobit('myKey','mySecret','example@gmail.com');
$balance = $leobitApi->getBalance();
$orderResult = $leobitApi->placeOrder('sell', '100', '0.001', 'btc');
``` 
