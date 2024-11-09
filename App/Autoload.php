<?php

class Autoload
{
  /**
   * Method used to register the autoloader
   */
  static function register()
  {
    spl_autoload_register(array(__CLASS__, 'autoload'));
  }

  /**
   * Method used to autoload classes
   *
   * @param string $class - class to load
   */
  static function autoload($class)
  {
    $class = str_replace('\\', '/', $class);
    if (file_exists(__DIR__.'/'.$class.'.php')) {
      require __DIR__.'/'.$class.'.php';
    }
  }
}