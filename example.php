<?php
require 'vendor/autoload.php';

// Send a "on" command to your powerplug-device
$myHome = new TahomaController();
$myHome->setUserId('**********@*****.com');
$myHome->setPassword('*******');
$devices = $myHome->getDevices();

$myLampLivingRoom = $devices[3];
$myHome->sendCommand($myLampLivingRoom->deviceURL, 'on', 0);
