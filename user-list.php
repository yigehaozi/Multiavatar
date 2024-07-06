<?php
/*
Template Name: 用户列表
*/

get_header();

$args = array(
    'orderby' => 'registered',
    'order' => 'DESC',
    'number' => -1,
);

$users = get_users( $args );
?>
<div class="user-list">
    <h1>用户列表</h1>
    <ul>
        <?php foreach ( $users as $user ) : ?>
            <li>
                <a href="<?php echo esc_url( get_author_posts_url( $user->ID ) ); ?>">
                    <?php echo esc_html( $user->display_name ); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php
get_footer();
