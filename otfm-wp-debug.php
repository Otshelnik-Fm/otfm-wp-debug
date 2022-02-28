<?php

/*
  MU Plugin Name: OtFm WP-Debug
  Plugin URI: https://github.com/Otshelnik-Fm/otfm-wp-debug
  Description: OtFm WP-Debug is a MU-WordPress plugin for debug
  Version: 1.0.0
  Author: Otshelnik-Fm
  Author URI: https://otshelnik-fm.ru/
  License: MIT
 */

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩

 */


/**
 * vd() (like var_dump) - convenient debugging instead of print_r or var_dump
 *
 * @param   $var    mixed   variable of debug.
 * @param   $fixed  bool    pass true to output in a fixed window at the top left.
 * @param   $excludes   string|array key|keys who exclude
 *
 * @return  string  var_dump variable.
 * @since   1.0.0
 */
if ( ! function_exists( 'vd' ) ) {
	function vd( $var, $fixed = false, $excludes = [] ) {

		if ( is_string( $fixed ) || is_array( $fixed ) ) {
			$excludes = $fixed;
			$fixed    = false;
		}

		if ( ! empty( $excludes ) && ( is_array( $var ) || is_object( $var ) ) ) {
			$data = [];

			$excludes = is_string( $excludes ) ? explode( ' ', $excludes ) : $excludes;

			foreach ( $var as $key => $subArr ) {
				if ( is_array( $subArr ) ) {
					foreach ( $excludes as $exclude ) {
						unset( $subArr[ $exclude ] );
					}
				} else {
					foreach ( $excludes as $exclude ) {
						unset( $subArr->$exclude );
					}
				}
				$data[ $key ] = $subArr;
			}

			otfm_wpdbg_print_data( $data, $fixed );
		} else {
			otfm_wpdbg_print_data( $var, $fixed );
		}
	}

}

function otfm_wpdbg_print_data( $var, $fixed ) {
	$det_style = '';
	if ( $fixed ) {
		$det_style = 'style="position:fixed;top:48px;left:18px;z-index:5000;"';

		echo '<style>.wpd_dark_mode pre{height:calc(85vh - 50px);overflow:auto;max-width:calc(100vw - 72px);}</style>';
	}
	echo '<style>
.wpd_dark_mode {padding:12px 6px;font-size:14px;line-height:1;background:#323641;color:#e3e8ef;}
.wpd_dark_mode pre {background:linear-gradient(to right, #171721, #163734) !important;
border:1px solid #040607;color:#e3e8ef !important;padding:12px !important;margin:12px 0 !important;font:normal normal 16px/normal monospace;}
</style>';

	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<details open class="wpd_dark_mode" ' . $det_style . '><pre>';

	if ( ! empty( $var ) ) {
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.PHP.DevelopmentFunctions.error_log_print_r
		echo htmlspecialchars( print_r( $var, true ) );
	} else {
		//phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
		var_dump( $var );
	}
	echo '</pre></details>';
}


/**
 * vda() (var_dump admin) - output to the screen for admin only
 *
 * @param   $var    mixed   variable of debug.
 * @param   $fixed  bool    pass true to output in a fixed window at the top left.
 *
 * @return  string  var_dump variable.
 * @since   1.0.0
 */
if ( ! function_exists( 'vda' ) ) {
	function vda( $var, $fixed = false ) {
		if ( current_user_can( 'manage_options' ) ) {
			vd( $var, $fixed );
		}
	}
}


/**
 * vdd() analog of vd, but with a die; at the end. When should I stop further work
 *
 * @param   $var    mixed   variable of debug.
 *
 * @return  string  var_dump variable.
 * @since   1.0.0
 */
if ( ! function_exists( 'vdd' ) ) {
	/** @noinspection PhpNoReturnAttributeCanBeAddedInspection */
	function vdd( $var ) {
		vd( $var );
		die;
	}
}


/**
 * vdl() (var_dump log) - we write to the server logs. When we can't display it on the screen (or this is the ajax request debug, for example).
 *
 * @param   $var    mixed   variable of debug.
 *
 * @since   1.0.0
 */
if ( ! function_exists( 'vdl' ) ) {
	function vdl( $var ) {
		//phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
		error_log( print_r( $var, true ) );
	}
}


/**
 * vdx() (var_dump XHR) - for ajax debugging (see incoming POST data on the browser's XHR tab) Example: https://yadi.sk/i/CPGuKgwmSQTEKg
 *
 * @param   $var    mixed   variable of debug.
 *
 * @since   1.0.0
 */
if ( ! function_exists( 'vdx' ) ) {
	function vdx( $var ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( is_array( $var ) ) {
				$var['data_type'] = gettype( $var );
			}
			if ( is_object( $var ) ) {
				$var->data_type = gettype( $var );
			} else if ( is_string( $var ) || is_int( $var ) || is_float( $var ) || is_bool( $var ) ) {
				$var .= ' | data_type: ' . gettype( $var );
			} else if ( null === $var ) {
				$var = 'NULL';
				$var .= ' | data_type: NULL';
			}

			wp_send_json_error( $var );
		}
	}
}

