<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('You are not allowed to call this page directly.');

if ( ! current_user_can('manage_options') ){
    wp_die(
        __(
            'You do not have sufficient permissions to manage options for this blog.',
            MP_DOMAIN
        )
    );
}
$sections = array_keys(MangaPress_Options::options_sections());
?>
<div class="wrap">
    <h1><?php echo get_admin_page_title() ?></h1>
    <form action="options.php" method="post" id="mangapress_options_form">
        <?php settings_fields('mangapress_options'); ?>
        <?php
            foreach ($sections as $section) {
                do_settings_sections("mangapress_options-{$section}");
            }
        ?>

        <p>
            <?php submit_button(); ?>
        </p>

    </form>
</div>