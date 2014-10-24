9xb
===

9xb delivery estimate class
you can include the DeliveryEstimate.php class in any php script and check for class->error or class->deliveryDate for expected delivery

if using the cli version from unix environment you need to chmod
```
chmod +x cli.php
```
if not using unix you need to call cli.php via php

windows
```
php cli.php 2014-02-25
```

unix
```
./cli.php 2014-02-25
```

if using a specific time you need to quote the parameter
```
./cli.php "2014-02-25 15:00:00"
```
