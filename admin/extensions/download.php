<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 2-1-2010 22:5
 */

if( ! defined( 'NV_IS_FILE_EXTENSIONS' ) ) die( 'Stop!!!' );

$contents = '';

$array = $nv_Request->get_string( 'data', 'post', '' );
$array = $array ? nv_base64_decode( $array ) : '';
if( $array and is_serialized_string( $array ) )
{
	$array = @unserialize( $array );
}
else
{
	$array = array();
}

$request = array();
$request['id'] = isset( $array['id'] ) ? intval( $array['id'] ) : 0;
$request['fid'] = isset( $array['compatible']['id'] ) ? intval( $array['compatible']['id'] ) : 0;

// Fixed request
$request['lang'] = NV_LANG_INTERFACE;
$request['basever'] = $global_config['version'];
$request['mode'] = 'download';

if( empty( $request['id'] ) or empty( $request['fid'] ) or ! isset( $array['tid'] ) )
{
	$contents = "ERR|" . $lang_module['download_error_preparam'];
}
else
{
	if( $array['tid'] == 2 )
	{
		$filename = NV_TEMPNAM_PREFIX . 'theme' . md5( $global_config['sitekey'] . session_id() ) . '.zip';
	}
	else
	{
		$filename = NV_TEMPNAM_PREFIX . 'auto_' . md5( $global_config['sitekey'] . session_id() ) . '.zip';
	}
	
	require( NV_ROOTDIR . '/' . NV_ADMINDIR . '/extensions/extensions.class.php' );
	$NV_Extensions = new NV_Extensions( $global_config, NV_TEMP_DIR );
	
	$args = array(
		'headers' => array(
			'Referer' => NUKEVIET_STORE_APIURL,
		),
		'stream' => true,
		'filename' => NV_ROOTDIR . '/' . NV_TEMP_DIR . '/' . $filename,
		'body' => $request,
	);	
	
	// Delete temp file if exists
	if( file_exists( NV_ROOTDIR . '/' . NV_TEMP_DIR . '/' . $filename ) )
	{
		@nv_deletefile( NV_ROOTDIR . '/' . NV_TEMP_DIR . '/' . $filename );
	}
	
	$array = $NV_Extensions->post( NUKEVIET_STORE_APIURL, $args );
	
	if( ! empty( $NV_Extensions::$error ) )
	{
		$contents = "ERR|" . nv_extensions_get_lang( $NV_Extensions::$error );
	}
	elseif( empty( $array['filename'] ) or ! file_exists( $array['filename'] ) )
	{
		$contents = "ERR|" . $lang_module['download_error_save'];
	}
	else
	{
		$contents = 'OK|' . $filename;
	}
}

echo $contents;