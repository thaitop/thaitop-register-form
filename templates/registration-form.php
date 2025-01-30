<?php defined('ABSPATH') || exit; ?>

<form id="thaitop-registration-form" class="thaitop-form" method="post">    
    <div class="form-group">
        <label for="username"><?php esc_html_e('Username *', 'thaitop-register-form'); ?></label>
        <input type="text" name="username" id="username" required />
    </div>
    
    <div class="form-group">
        <label for="email"><?php esc_html_e('Email *', 'thaitop-register-form'); ?></label>
        <input type="email" name="email" id="email" required />
    </div>
    
    <div class="form-group">
        <label for="password"><?php esc_html_e('Password *', 'thaitop-register-form'); ?></label>
        <input type="password" name="password" id="password" required />
    </div>
    
    <?php $this->render_custom_fields(); ?>
    
    <?php do_action('thaitop_register_form_after_fields'); ?>
    <?php wp_nonce_field('thaitop_registration_nonce'); ?>
    
    <button type="submit" name="thaitop_register_submit">
        <?php esc_html_e('Register', 'thaitop-register-form'); ?>
    </button>
</form>
