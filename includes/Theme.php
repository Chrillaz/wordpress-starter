<?php

namespace Chrillaz;

if ( ! defined( 'ABSPATH' ) ) exit;

use Chrillaz\Loader;

use Chrillaz\Assets;

abstract class Theme {

  // private $assets;

  private $loader;

  private $assets;

  private $customizer;

  private $settings;

  protected static $optionsName = '';

  // public function __construct () {
  
  //   $this->settings = apply_filters( 'chrillaz/default_settings', asset( '/../settings.json', false, true )->contents );

  //   $this->loader = new Loader();

  //   $this->customizer = new Customizer( $this->settings['options'] );

  //   $this->assets = new AssetLoader();
  // }

  /**
   * getSetting
   * 
   * reads the settings json and returns the chuck of json the path is targeting
   * 
   * @param string $param
   * 
   * @return mixed
   */
  protected function getSetting ( string $part ) {

    if ( $this->settings === null ) {

      $settings = \apply_filters( 
        'chrillaz/default_settings', 
        json_decode( file_get_contents( $this->assets()->path( '/settings.json' )->path ), true ) 
      );

      $this->settings = $settings;
    }

    return ( isset( $this->settings[$part] ) ? $this->settings[$part] : [] );
  }

  /**
   * customizer
   * 
   * returns an instance of Customizer
   * this class handles the setup of themes options panel in the customizer
   * 
   * @param array $settings
   * 
   * @return Customizer
   */
  protected function customizer ( array $settings ) {

    if ( $this->customizer === null ) $this->customizer = new Customizer( $settings );

    return $this->customizer;
  }

  /**
   * loader
   * 
   * returns an instance of Loader
   * this class queues action and filter hooks
   * 
   * @return Loader
   */
  public function loader () {

    if ( $this->loader === null ) $this->loader = new Loader();

    return $this->loader;
  }

  /**
   * asset
   * 
   * returns an instance of Assets
   * this class handles script and style inclutions and script executions
   * 
   * @return Assets
   */
  public function assets () {

    if ( $this->assets === null ) $this->assets = new Assets();

    return $this->assets;
  }

  /**
   * getOptions
   * 
   * a wrapper around get_option wich provides the theme default options
   * 
   * @return array
   */
  public static function getOptions (): array {

    return get_option( self::$optionsName, self::getSetting( 'default-options' ) );
  }

  /**
   * getOption
   * 
   * gets the specifyed option from the options array
   * 
   * @param string $option
   * 
   * @return mixed
   */
  public static function getOption ( string $option ) {

    $options = self::getOptions();

    return ( isset( $options[$option] ) ? $options[$option] : false );
  }

  /**
   * getTheme
   * 
   * Returns value from theme header details
   * 
   * @param string $detail
   * 
   * @return string
   */
  public static function getTheme ( string $detail ): string {

    return wp_get_theme()->get( $detail );
  }

  /**
   * getThemeMod
   * 
   * Returns theme mod value
   * 
   * @param string $mod
   * 
   * @param mixed $default
   * 
   * @return mixed
   */
  public static function getThemeMod ( string $mod, $default = false ) {

    if ( ! $default ) {

      return get_theme_mod( $mod, $default );
    }

    return get_theme_mod( $mod, $this->getCustomizer()->getDefault( $mod ) );
  }

  /**
   * getColorScheme
   * 
   * Returns gutenber compatible color array
   * 
   * @return array
   */
  public static function getColorScheme (): array {

    return array_map( function ( $name ) {

      return [
        'name'  => __( ucfirst( str_replace( '-', ' ',  $name ) ), $this->getTheme( 'TextDomain' ) ),
        'slug'  => $name,
        'color' => esc_html( get_theme_mod( $name, $this->customizer->getDefault( $name ) ) )
      ];
    }, array_keys( $this->settings['options']['colors'] ) );
  }
}