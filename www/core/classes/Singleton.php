<?php
#################################
#       GydruS's Engine 3       #
#       "Singleton" class       #
#             v. 1.0            #
#           2012 10 09          #
#################################

#################################
# Description
#--------------------------------
# 
# 

class Singleton
{
    protected static $_instance;
 
    // Закрываем доступ к функциям вне класса.
    private function __construct(){
    }
 
    private function __clone(){
    }
	
    public static function getInstance() {
        // проверяем актуальность экземпляра
        if (self::$_instance === null) {
            // создаем новый экземпляр
            self::$_instance = new self();
        }
        // возвращаем созданный или существующий экземпляр
        return self::$_instance;
    }
	
}
