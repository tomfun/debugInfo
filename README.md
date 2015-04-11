# debugInfo lightweight module for Magento #

### Alternative way to control your web application: debug, dump variables and measure performance ###
If you tired use some combination like this
```php
var_dump($some);
die();
```
And want some more comfortable, or you can`t use debugger, and still want to see some data intermediate data when using Ajax this for You.
### Installation ###
- Copy app directory to your magento project
- Login (and maybe relogin) to admin panel
- Configure this in your own way (based on your security needs or habits) Magento Admin -> System -> Configuration -> Brander -> Core -> Custom Debug Info Settings 

### Usage ###
#### echo ####
In your code type some like this:
```php
Tommy_DebugInfo_Helper_Data::getMe()->addDebugOutput('Message section', 'My debug mess :)');
```
Then go to web browser and see there
![Image of output](https://github.com/tomfun/debugInfo/blob/master/docs/imgs/80fb3b2a91.jpg)

In this example I use this configuration for module:
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
![Image of output](https://github.com/tomfun/debugInfo/blob/master/docs/imgs/0b63dd7d9d.jpg)

#### Time measure ####
```php
$performance->addPerformanceLog('sleep 5 sec');
sleep(5);
$performance->addPerformanceLog('sleep 5 sec', 'finish');
```
You`ll see in browser your data
If you want some intermediate data for your large process
```php
$performance->addPerformanceLog('sleep 5 sec');
sleep(3);
$performance->addPerformanceLog('sleep 5 sec', 'breakpoint');
sleep(2);
$performance->addPerformanceLog('sleep 5 sec', 'finish');
```
![Image of output](https://github.com/tomfun/debugInfo/blob/master/docs/imgs/c0a66ccd61.jpg)

#### Compare cached blocks with real render ####
To check block caching is correct, you can use option in config part
### Work modes ###
- Frontend
- Special url postfix
- Session

#### Frontend ####
It works like described in screen outputs above. You see data in the bottom of browser page. This mode not recomended for production environment.

#### Special url postfix ####
Like "Frontend" but use additional get parameter. Without this parameter we`ll get default behavior for pages.

#### Session ####
In browser special page:
*http://url your.web.site/debug_info*
you\`ll see list of connection (requests) to site.
Browse to link you need then see all data like in "Frontend" mode.
This mode require magento cache, there placed all data, you can clear magento cache this lead to clear debugInfo`s data

### Requirements ###
- Magento (tested only in 1.9)
- jQuery (and set path to it in cofig, tested in 1.10*)
- Browesr (tested in Chrome and Mozilla)
- PHP (tested in 5.5, must work in 5.3)
