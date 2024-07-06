<?php
/**
 * Plugin Name: Multiavatar随机头像
 * Plugin URI: https://6.ke/
 * Description: 为 WordPress 用户生成独特、多彩的头像。
 * Version: 1.0
 * Author: 网上邻居
 * Author URI: https://6.ke/
 * License: GPL2
 */

// 直接访问退出。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 定义常量。
define( 'MULTIAVATAR_DIR', plugin_dir_path( __FILE__ ) );
define( 'MULTIAVATAR_URL', plugin_dir_url( __FILE__ ) );

// 包含文件。
require_once MULTIAVATAR_DIR . 'multiavatar-admin.php';
require_once MULTIAVATAR_DIR . 'multiavatar-functions.php';

// 注册激活钩子。
register_activation_hook( __FILE__, 'multiavatar_activate' );

// 注册停用钩子。
register_deactivation_hook( __FILE__, 'multiavatar_deactivate' );

// 注册卸载钩子。
register_uninstall_hook( __FILE__, 'multiavatar_uninstall' );
