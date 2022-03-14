<?php

/*
  MU Plugin Name: OtFm WP-Debug
  Plugin URI: https://github.com/Otshelnik-Fm/otfm-wp-debug
  Description: OtFm WP-Debug is a MU-WordPress plugin for debug
  Version: 1.1.0
  Author: Otshelnik-Fm
  Author URI: https://otshelnik-fm.ru/
  License: MIT
 */

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩

 */

global $wpdbg_settings;

/**
 * Left debug panel
 * You can also open the panel via a GET-request - site.com/?wpdbg
 *
 * false - disabled;
 * true - enabled all (in dev environment);
 * 'admin' - if visible in admin;
 */
$wpdbg_settings['left_panel'] = false;

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

/***************************************************************
 *
 * Left query panel
 *
 */

function wpdbg_panel_activated() {
	if ( isset( $_GET['wpdbg'] ) ) {
		return true;
	}

	global $wpdbg_settings;

	if ( isset( $wpdbg_settings['left_panel'] ) && false == $wpdbg_settings['left_panel'] ) {
		return false;
	}

	if ( isset( $wpdbg_settings['left_panel'] ) && ! empty( $wpdbg_settings['left_panel'] ) ) {
		if ( true === $wpdbg_settings['left_panel'] ) {
			return true;
		} elseif ( 'admin' === $wpdbg_settings['left_panel'] && current_user_can( 'activate_plugins' ) ) {
			return true;
		}
	}

	return false;
}

// выводим блок
add_action( 'wp_footer', 'wpdbg_panel' );
function wpdbg_panel() {
	if ( ! wpdbg_panel_activated() ) {
		return false;
	}

	$recourses = wpdbg_meter_styles();
	$recourses .= wpdbg_meter_scripts();

	echo $recourses . '<div id="wpdbg_box">' . wpdbg_meter() . '</div>';
}

// метрика
function wpdbg_meter() {
	$out = '<div id="wpdbg_mem" title="PHP time (sec)">' . timer_stop() . '</div>';
	$out .= '<div id="wpdbg_m_old" title="Old data"></div>';
	$out .= '<div id="wpdbg_db" title="SQL">' . get_num_queries() . '</div>';
	$out .= '<div id="wpdbg_q_old" title="Old data"></div>';
	if ( function_exists( 'memory_get_usage' ) ) {
		$out .= '<div id="wpdbg_mb" title="MB">' . round( memory_get_usage() / 1024 / 1024, 3 ) . '</div>';
		$out .= '<div id="wpdbg_w_old" title="Old data"></div>';
	}

	return $out;
}

function wpdbg_meter_styles() {
	$style = '
#wpdbg_box {
	animation: .5s wpdbgA forwards .5s;
	background: rgba(0, 0, 0, .8);
	border-left: 6px solid #70b340;
	border-radius: 0 3px 3px 0;
	color: #89d549;
	font: 16px/1.4 arial;
	left: 0;
	opacity: 0;
	padding: 6px 12px;
	position: fixed;
	bottom: 50vh;
	z-index: 100;
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 0 6px;
}
@keyframes wpdbgA{100%{opacity:1;}}
#wpdbg_box > div:nth-child(2n) {
	color: #70b340;
	font-size: 13px;
	margin-top: 6px;
}
@media screen and (max-width:800px) {
    #wpdbg_box {
        left: -130px;
        transition: all .3s ease-in;
    }
    #wpdbg_box:hover {
        left: 0;
    }
	#wpdbg_box::after {
		content: "#";
		left: 0;
		position: fixed;
		background: rgba(0, 0, 0, .8);
		font-size: 26px;
		padding: 0 9px;
		margin: 12px 0 0;
		bottom: calc(50vh - 36px);
	}
}
';

	return '<style>' . wpdbg_compress_css( $style ) . '</style>';
}

function wpdbg_meter_scripts() {
	$js = '
window.addEventListener("unload", function(event) {
    let wpdbgStore = [];
    
    let time = document.getElementById("wpdbg_mem");
    let queries = document.getElementById("wpdbg_db");
    let mb = document.getElementById("wpdbg_mb");
    
    wpdbgStore[0] = time.innerHTML;
    wpdbgStore[1] = queries.innerHTML;
    wpdbgStore[2] = mb.innerHTML;

    wpdbgCreateCookie("wpdbgStoreMem", JSON.stringify( wpdbgStore ) )
} );
document.addEventListener("DOMContentLoaded", function(event) { 
    let cook = wpdbgGetCookie("wpdbgStoreMem");
    if(undefined === cook){
        return;
    }
    
    let data = JSON.parse(cook);
    if ( ( data !== undefined ) && ( data !== null ) ) {
        let time = document.getElementById("wpdbg_m_old");
        let queries = document.getElementById("wpdbg_q_old");
        let mb = document.getElementById("wpdbg_w_old");
        
        time.textContent = "(" + data[0] + ")";
        queries.textContent = "(" + data[1] + ")";
        mb.textContent = "(" + data[2] + ")";
    }
} );
function wpdbgCreateCookie(name, value ) {
    let date = new Date();
    date.setTime(date.getTime() + (7 * 24 * 60 * 60 * 1000));
    let expires = "; expires=" + date.toGMTString();

    document.cookie = name + "=" + value + expires + "; path=/";
}
function wpdbgGetCookie(name) {
  let matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, "\\$1") + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}
';

	return '<script>' . wpdbg_compress_js( $js ) . '</script>';
}

function wpdbg_compress_js( $js ) {
	return preg_replace( '/ {2,}/', '', str_replace( [ "\r\n", "\r", "\n", "\t" ], '', $js ) );
}

function wpdbg_compress_css( $src ) {
	$src_cleared   = preg_replace( '/ {2,}/', '', str_replace( [ "\r\n", "\r", "\n", "\t", '  ', '   ', '    ' ], '', $src ) );
	$src_non_space = str_replace( ': ', ':', $src_cleared );

	return str_replace( ' {', '{', $src_non_space );
}
/************************ END ************************/
