<?php
#################################
#  GydruS's Engine 3: Codename  #
#         v. 0.0.0 Alpha        #
#    2012 10 08 - 2012 00 00    #
#################################

#################################
# Initialization                 

include_once 'constants.php';
include_once 'loader.php';
Load::lib('ge_csv');    // CSV Functions
Load::lib('fs_helper'); // FileSytem Helper
setlocale(LC_ALL,'en_US.UTF-8');

$core = new Core();

$core->autoloadModules = explode(',', $core->config->read('autoloadModules', ''));
$core->defaultModule = $core->config->read('defaultModule', 'main');
$core->finalizingModules = explode(',', $core->config->read('finalizingModules', ''));

$dbUser = $core->config->read('dbUser');
if (!empty($dbUser)) {
    $db = new DB('mysqli', array(&$core, 'dbErrorCallback'));
    $db->dbTablesPrefix = $core->config->read('dbTablesPrefix', '');
    $db->connect($core->config->read('dbHost'), $core->config->read('dbUser'), $core->config->read('dbPass'));
    $db->selectDB($core->config->read('dbName'));
}

$cacheAddress = $core->config->read('cacheAddress');
if (!empty($cacheAddress)) {
    $cache = new Cache('redis', array(&$core, 'cacheErrorCallback'));
    $cache->connect($core->config->read('cacheAddress'), $core->config->read('cacheUser'), $core->config->read('cachePass'));
}

$core->run();
