<?php
/**
 * Page installer
 *
 * @since 2.3
 */
class WPFEP_Admin_Installer {

    function __construct() {
        add_action( 'admin_notices', array($this, 'admin_notice') );
        add_action( 'admin_init', array($this, 'handle_request') );
    }

    /**
     * Print admin notices
     *
     * @return void
     */
    function admin_notice() {
        $page_created = get_option( '_wpfep_page_created' );
           ?>
           <?php
        if ( $page_created == '0') {
             ?>
             <div class="updated error">
                 <p>
                     <?php _e( 'If you have not created <strong>WP User Frontend</strong> pages yet, you can do this by one click.', 'wpptm' ); ?>
               </p>
             <p class="submit">
                   <a class="button button-primary" href="<?php echo add_query_arg( array( 'install_wpfep_pages' => true ), admin_url( 'admin.php?page=wpfep-settings' ) ); ?>"><?php _e( 'Install wpfep Pages', 'wpptm' ); ?></a>
                  <?php _e( 'or', 'wpptm' ); ?>
                    <a class="button" href="<?php echo add_query_arg( array( 'wpfep_hide_page_nag' => true ) ); ?>"><?php _e( 'Skip Setup', 'wpptm' ); ?></a>
               </p>
            </div>
           <?php
         }

        if ( isset( $_GET['wpfep_page_installed'] ) && $_GET['wpfep_page_installed'] == '1' ) {
            ?>
            <div class="updated">
                <p>
                    <strong><?php _e( 'Congratulations!', 'wpptm' ); ?></strong> <?php _e( 'Pages for <strong>WP User Frontend</strong> has been successfully installed and saved!', 'wpptm' ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Handle the page creation button requests
     *
     * @return void
     */
    function handle_request() {
        if ( isset( $_GET['install_wpfep_pages'] ) && $_GET['install_wpfep_pages'] == '1' ) {
            $this->init_pages();
        }

        if ( isset( $_POST['install_wpfep_pages'] ) && $_POST['install_wpfep_pages'] == '1' ) {
            $this->init_pages();
        }

        if ( isset( $_GET['wpfep_hide_page_nag'] ) && $_GET['wpfep_hide_page_nag'] == '1' ) {
            update_option( '_wpfep_page_created', '1' );
        }
    }

    /**
     * Initialize the plugin with some default page/settings
     *
     * @since 2.2
     * @return void
     */
    function init_pages() {

        // create a Registor page
        $register_page = $this->create_page( __( 'Register', 'wpptm' ), '[wpfep-register]' );
        // edit Account
        $edit_page      = $this->create_page( __( 'Edit', 'wpptm' ), '[wpfep]' );
        // login page
        $login_page     = $this->create_page( __( 'Login', 'wpptm' ), '[wpfep-login]' );
        // profile page
        $profile_page     = $this->create_page( __( 'Profile', 'wpptm' ), '[wpfep-profile]' );

        // save the settings
        if ( $edit_page ) {
            update_option( 'wpfep_frontend_posting', array(
                'edit_page_id'      => $edit_page,
                'default_post_form' => $post_form
            ) );
        }

        // profile pages
        $profile_options = array();
        $reg_page = false;

        if ( $login_page ) {
            $profile_options['login_page'] = $login_page;
        }

        $data = apply_filters( 'wpfep_pro_page_install', $profile_options );

        if ( is_array( $data ) ) {

            if ( isset ( $data['profile_options'] ) ) {
                $profile_options = $data['profile_options'];
            }
            if ( isset ( $data['reg_page'] ) ) {
                $reg_page = $data['reg_page'];
            }
        }

        if ( $login_page && $reg_page ) {
            $profile_options['register_link_override'] = 'on';
        }

        update_option( 'wpfep_profile', $profile_options );

        update_option( '_wpfep_page_created', '1' );

        wp_redirect( admin_url( 'admin.php?page=wpfep-settings&wpfep_page_installed=1' ) );
        exit;
    }

    /**
     * Create a page with title and content
     *
     * @param  string $page_title
     * @param  string $post_content
     * @return false|int
     */
    function create_page( $page_title, $post_content = '', $post_type = 'page' ) {
        $page_id = wp_insert_post( array(
            'post_title'     => $page_title,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'post_content'   => $post_content
        ) );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            return $page_id;
        }

        return false;
    }
}