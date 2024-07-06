<?php
/*
Template Name: 更换头像
*/

get_header();

$user_id = get_current_user_id();

if ( isset( $_POST['submit'] ) ) {
    $upload_dir = wp_upload_dir()['basedir'] . '/' . get_option( 'multiavatar_upload_dir', 'multiavatar' );
    $avatar_file = $upload_dir . '/' . $user_id . '.svg';

    if ( ! empty( $_FILES['avatar']['tmp_name'] ) && file_exists( $_FILES['avatar']['tmp_name'] ) ) {
        require_once MULTIAVATAR_DIR . 'multiavatar.php';

        $multiavatar = new Multiavatar();
        $multiavatar->set_api_key( get_option( 'multiavatar_api_key' ) );

        $result = $multiavatar->generate( $_FILES['avatar']['tmp_name'], $avatar_file );

        if ( $result === true ) {
            update_user_meta( $user_id, 'multiavatar', $user_id . '.svg' );
            wp_redirect( home_url() );
            exit;
        }
    }
}
?>
<style>
.row {
    display: flex;
    flex-wrap: wrap;
}

.column {
    flex: 50%;
    padding: 10px;
}
</style>
<div class="change-avatar">
    <h1>更换头像</h1>
    <div class="row">
        <div class="column">
            <h2>选择性更换头像</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="avatar" />
                <input type="submit" name="submit" value="上传" />
            </form>
        </div>
        <div class="column">
            <h2>批量更换头像</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="avatars[]" multiple />
                <input type="submit" name="submit" value="上传" />
            </form>
        </div>
    </div>
</div>
<?php
get_footer();
?>
