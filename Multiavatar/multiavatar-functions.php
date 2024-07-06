<?php
/**
 * Multiavatar 插件的函数。
 */

// 直接访问退出。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 激活插件。
function multiavatar_activate() {
    // 添加默认设置。
    add_option( 'multiavatar_api_key', '' );
    add_option( 'multiavatar_upload_dir', 'multiavatar' );
}

// 停用插件。
function multiavatar_deactivate() {
    // 什么也不做。
}

// 卸载插件。
function multiavatar_uninstall() {
    // 删除设置。
    delete_option( 'multiavatar_api_key' );
    delete_option( 'multiavatar_upload_dir' );
}

// 生成 Multiavatar。
function multiavatar_generate( $email ) {
    $api_key = get_option( 'multiavatar_api_key' );
    $upload_dir = get_option( 'multiavatar_upload_dir' );

    $hash = md5( strtolower( trim( $email ) ) );
    $url = 'https://api.multiavatar.com/' . $hash . '.svg?apikey=' . $api_key;

    $transient_name = 'multiavatar_' . $hash;
    $avatar_url = get_transient( $transient_name );
    if ( false === $avatar_url ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        $content = curl_exec( $ch );
        curl_close( $ch );

        if ( $content ) {
            $filename = $hash . '.svg';
            $filepath = ABSPATH . $upload_dir . '/' . $filename;
            if ( ! file_exists( $filepath ) ) {
                wp_mkdir_p( dirname( $filepath ) );
            }
            file_put_contents( $filepath, $content );
            $avatar_url = site_url( '/' . $upload_dir . '/' . $filename );
            set_transient( $transient_name, $avatar_url, 7 * DAY_IN_SECONDS );
        }
    }

    return $avatar_url;
}

// 获取用户头像。
function multiavatar_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
    $email = '';
    if ( is_numeric( $id_or_email ) ) {
        $user = get_user_by( 'id', $id_or_email );
        if ( $user ) {
            $email = $user->user_email;
        }
    } elseif ( is_object( $id_or_email ) ) {
        if ( ! empty( $id_or_email->user_email ) ) {
            $email = $id_or_email->user_email;
        }
    } else {
        $email = $id_or_email;
    }

    if ( $email ) {
        $avatar_url = multiavatar_generate( $email );
        if ( $avatar_url ) {
            $avatar = '<img src="' . $avatar_url . '" class="avatar avatar-' . $size . ' photo" width="' . $size . '" height="' . $size . '" alt="' . esc_attr( $alt ) . '" />';
        }
    }

return '<span class="avatar-img"><img src="' . multiavatar_generate( $email ) . '" class="avatar avatar-' . $size . ' photo" width="' . $size . '" height="' . $size . '" alt="' . esc_attr( $alt ) . '" /></span>';
}
add_filter( 'get_avatar', 'multiavatar_get_avatar', 10, 5 );

// 操作成功或失败的提示。
function multiavatar_admin_notice( $message, $type = 'success' ) {
    $class = 'notice notice-' . $type;
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

// 记录插件操作日志。
function multiavatar_log( $message ) {
    $log_file = MULTIAVATAR_DIR . 'multiavatar.log';
    $date = date( 'Y-m-d H:i:s' );
    $log_message = "[$date] $message\n";
    error_log( $log_message, 3, $log_file );
}

// 获取用户列表。
function multiavatar_get_users() {
    $users = get_users();
    $user_list = array();
    foreach ( $users as $user ) {
        $user_list[] = array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'avatar' => get_avatar( $user->ID ),
            'multiavatar' => multiavatar_get_avatar( '', $user->user_email, 96, '', $user->display_name ),
        );
    }
    return $user_list;
}

// 显示用户列表。
function multiavatar_show_user_list() {
    $users = multiavatar_get_users();
    ?>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( '用户ID', 'multiavatar' ); ?></th>
                <th><?php _e( '用户名', 'multiavatar' ); ?></th>
                <th><?php _e( '邮箱', 'multiavatar' ); ?></th>
                <th><?php _e( '现在头像', 'multiavatar' ); ?></th>
                <th><?php _e( '更换后头像', 'multiavatar' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $users as $user ) : ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['avatar']; ?></td>
                    <td><?php echo $user['multiavatar']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

// 显示选择性更换头像表单。
function multiavatar_show_single_form() {
    ?>
    <h2><?php _e( '选择性更换头像', 'multiavatar' ); ?></h2>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( '选择用户', 'multiavatar' ); ?></th>
                <td>
                    <select name="user_id">
                        <option value=""><?php _e( '请选择用户', 'multiavatar' ); ?></option>
                        <?php foreach ( multiavatar_get_users() as $user ) : ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo $user['username'] . ' (' . $user['email'] . ')'; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( '更换头像', 'multiavatar' ); ?></th>
                <td>
                    <input type="checkbox" name="replace_avatar" value="1" />
                    <label><?php _e( '替换现有头像', 'multiavatar' ); ?></label>
                </td>
            </tr>
        </table>
        <?php wp_nonce_field( 'multiavatar_single', 'multiavatar_nonce' ); ?>
        <?php submit_button( __( '更换头像', 'multiavatar' ) ); ?>
    </form>
    <?php
}

// 显示批量更换头像表单。
function multiavatar_show_bulk_form() {
    ?>
    <h2><?php _e( '批量更换头像', 'multiavatar' ); ?></h2>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( '更换头像', 'multiavatar' ); ?></th>
                <td>
                    <input type="checkbox" name="replace_avatar" value="1" />
                    <label><?php _e( '替换现有头像', 'multiavatar' ); ?></label>
                </td>
            </tr>
        </table>
        <?php wp_nonce_field( 'multiavatar_bulk', 'multiavatar_nonce' ); ?>
        <?php submit_button( __( '更换头像', 'multiavatar' ) ); ?>
    </form>
    <?php
}

// 更换头像。
function multiavatar_replace_avatar( $user_id, $replace_avatar ) {
    $user = get_user_by( 'id', $user_id );
    if ( $user ) {
        $email = $user->user_email;
        $multiavatar_url = multiavatar_generate( $email );
        if ( $multiavatar_url ) {
            $user_avatar = get_avatar( $user_id );
            $user->set( 'user_avatar', $multiavatar_url );
            wp_update_user( $user );
            if ( $replace_avatar ) {
                $upload_dir = wp_upload_dir();
                $avatar_file = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $user_avatar );
                if ( file_exists( $avatar_file ) ) {
                    unlink( $avatar_file );
                }
            }
            multiavatar_admin_notice( __( '头像更换成功。', 'multiavatar' ) );
            multiavatar_log( sprintf( __( '用户 %d 更换了头像。', 'multiavatar' ), $user_id ) );
        } else {
            multiavatar_admin_notice( __( '头像生成失败，请检查 API 密钥。', 'multiavatar' ), 'error' );
        }
    }
}

// 处理选择性更换头像表单提交。
function multiavatar_process_single_form() {
    if ( isset( $_POST['multiavatar_nonce'] ) && wp_verify_nonce( $_POST['multiavatar_nonce'], 'multiavatar_single' ) ) {
        $user_id = intval( $_POST['user_id'] );
        $replace_avatar = isset( $_POST['replace_avatar'] );
        if ( $user_id ) {
            multiavatar_replace_avatar( $user_id, $replace_avatar );
        } else {
            multiavatar_admin_notice( __( '请选择用户。', 'multiavatar' ), 'error' );
        }
    }
}

// 处理批量更换头像表单提交。
function multiavatar_process_bulk_form() {
    if ( isset( $_POST['multiavatar_nonce'] ) && wp_verify_nonce( $_POST['multiavatar_nonce'], 'multiavatar_bulk' ) ) {
        $replace_avatar = isset( $_POST['replace_avatar'] );
        foreach ( multiavatar_get_users() as $user ) {
            multiavatar_replace_avatar( $user['id'], $replace_avatar );
        }
    }
}

// 插件初始化。
function multiavatar_init() {
    if ( isset( $_POST['multiavatar_single'] ) ) {
        multiavatar_process_single_form();
    }
    if ( isset( $_POST['multiavatar_bulk'] ) ) {
        multiavatar_process_bulk_form();
    }
}
add_action( 'init', 'multiavatar_init' );

// 添加 Multiavatar 页面到管理菜单。
function multiavatar_add_menu() {
    add_menu_page(
        'Multiavatar',
        'Multiavatar',
        'manage_options',
        'multiavatar',
        'multiavatar_page',
        'dashicons-admin-users'
    );
}
add_action( 'admin_menu', 'multiavatar_add_menu' );

// Multiavatar 页面。
function multiavatar_page() {
    ?>
    <div class="wrap">
        <h1>Multiavatar</h1>
        <?php multiavatar_show_user_list(); ?>
        <hr />
        <?php multiavatar_show_single_form(); ?>
        <hr />
        <?php multiavatar_show_bulk_form(); ?>
    </div>
    <?php
}

