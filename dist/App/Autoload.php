<?php

class Autoload
{
  static function register()
  {
    spl_autoload_register(array(__CLASS__, 'autoload'));
  }

  static function autoload($class)
  {
    $class = str_replace('\\', '/', $class);
    if (file_exists(__DIR__.'/'.$class.'.php')) {
      require __DIR__.'/'.$class.'.php';
    }
  }
}