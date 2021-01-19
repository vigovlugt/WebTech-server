<?php

namespace SpotiSync\Utils;

class Time {
  public static function getMs(){
    return round(microtime(true) * 1000);
  }
}
