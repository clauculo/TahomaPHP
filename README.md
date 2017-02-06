# TahomaPHP
Somfy Tahoma PHP module

# Example (1)
// Send a "on" command to your powerplug-device
$myHome = new TahomaController();
$myHome->setUserId('**********@*****.com');
$myHome->setPassword('*******');
$devices = $test->getDevices();
$myHome->sendCommand($devices[3]->deviceURL, 'on', 0);
