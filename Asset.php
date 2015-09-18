<?php namespace SBX;

abstract class Asset
{
  protected static $cache = array();

  public static function add($handle, $path, $deps = array(), $version = '1.0', $mixed = null)
  {
    // Set the version OR the cachebusted version.
    $version = (isset(static::$cache[$path])) ? static::$cache[$path] : $version;

    // Check if asset has an extension.
    $ext = pathinfo($path, PATHINFO_EXTENSION);

    // Check the type of asset.
    if ($ext && $ext == 'css') {
      wp_enqueue_style($handle, $path, $deps, $version, $mixed);
    } else {
      wp_enqueue_script($handle, $path, $deps, $version, $mixed);
    }

  }

}
