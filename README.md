# debugInfo lightweight module for Magento #

### Alternative way to control performance your web application, debug, dump variables and measure performance ###
If you tired use some combination like this
```php
var_dump($some);
die();
```
And want some more comfortable, or you can`t use debugger, and still want to see some data intermediate data when using Ajax this for You.
### Installation ###
- Copy directory app to your magento project
- Login (and maybe relogin) to admin panel
- Configure this in your own way (based on your security needs or habits)

### Usage ###
In your code type some like this:
```php
Tommy_DebugInfo_Helper_Data::getMe()->addDebugOutput('Message section', 'My debug mess :)');
```
Then go to web browser and see there
http://joxi.ru/gmvjOyYFJgg3ma

In this example I use configuration for module
"Include output force, if get this param" seted to "?showInfog"
in admin section

#### var_dump ####
```php
$performance = Tommy_DebugInfo_Helper_Data::getMe();
$someArrayOrObject = new stdClass();
$someArrayOrObject->name='Home';
$someArrayOrObject->status=1;
$performance->addDirectOutput($someArrayOrObject, 'someArrayOrObject');
```
You`ll see in Chrome console your object

#### Time measure ####
```php
$performance->addPerformanceLog('sleep 5 sec');
sleep(5);
$performance->addPerformanceLog('sleep 5 sec', 'finish');
```
You`ll see in browser your data
