<?php

// 添加插件设置菜单
function multiavatar_settings_menu() {
    add_options_page(
        'Multiavatar设置',
        'Multiavatar',
        'manage_options',
        'multiavatar-settings',
        'multiavatar_settings_page'
    );
}
add_action( 'admin_menu', 'multiavatar_settings_menu' );

// 创建插件设置页面
function multiavatar_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'multiavatar_settings' );
            do_settings_sections( 'multiavatar_settings' );
            submit_button( '保存设置' );
            ?>
        </form>
    </div>
    <?php
}

// 注册插件设置
function multiavatar_register_settings() {
    register_setting( 'multiavatar_settings', 'multiavatar_api_key' );
    register_setting( 'multiavatar_settings', 'multiavatar_upload_dir' );
}
add_action( 'admin_init', 'multiavatar_register_settings' );

// 添加插件设置字段
function multiavatar_settings_fields() {
    add_settings_section(
        'multiavatar_settings_section',
        'Multiavatar设置',
        '',
        'multiavatar_settings'
    );
    add_settings_field(
        'multiavatar_api_key',
        'API密钥',
        'multiavatar_api_key_field',
        'multiavatar_settings',
        'multiavatar_settings_section'
    );
    add_settings_field(
        'multiavatar_upload_dir',
        '头像保存目录',
        'multiavatar_upload_dir_field',
        'multiavatar_settings',
        'multiavatar_settings_section'
    );
}
add_action( 'admin_init', 'multiavatar_settings_fields' );

// 添加API Key字段
function multiavatar_api_key_field() {
    $value = get_option( 'multiavatar_api_key' );
    ?>
    <input type="text" name="multiavatar_api_key" value="<?php echo esc_attr( $value ); ?>" />
    <?php
}

// 添加Upload Directory字段
function multiavatar_upload_dir_field() {
    $value = get_option( 'multiavatar_upload_dir', 'multiavatar' );
    ?>
    <input type="text" name="multiavatar_upload_dir" value="<?php echo esc_attr( $value ); ?>" />
    <?php
}
