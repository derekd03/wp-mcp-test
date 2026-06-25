---
name: wordpress-helper
description: Guidelines and reusable prompt rules to fix common AI errors in WordPress plugin and site development, including database query sanitization, security nonces, output escaping, and hook timing.
---

# WordPress Helper Skill

This workspace skill helps prevent common mistakes made by AI when writing or refactoring WordPress PHP code. Whenever you write, modify, or debug WordPress code in this workspace, you must adhere to the rules and templates defined in this document.

---

## 1. Database Query Safety (Prevent SQL Injection)

AIs frequently write raw SQL queries by interpolating variables directly. You must ALWAYS use `$wpdb->prepare()` to sanitize all variables injected into database queries.

### Rules
- Never concatenate or interpolate PHP variables directly in SQL statements.
- Use `%d` for integers, `%f` for floats, and `%s` for strings.
- Never wrap placeholder tokens in quotes within the SQL string (e.g. use `%s`, not `'%s'`).

### Incorrect AI Code
```php
$user_id = $_GET['user_id'];
$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}custom_table WHERE user_id = " . $user_id);
```

### Corrected Code
```php
$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}custom_table WHERE user_id = %d",
        $user_id
    )
);
```

---

## 2. Security Check Enforcement (Nonces & Capabilities)

AIs often write processing handlers for GET, POST, or AJAX actions without verifying authorization. You must ALWAYS verify CSRF nonces and user capabilities before processing any action.

### Rules
- On form actions, verify nonces using `check_admin_referer()` or `wp_verify_nonce()`.
- Check user permissions using `current_user_can()` with appropriate capabilities (e.g., `manage_options`, `edit_posts`).
- Sanitize input parameters immediately using functions like `sanitize_text_field()`, `absint()`, or `filter_var()`.

### Incorrect AI Code
```php
if (isset($_POST['delete_item_id'])) {
    my_plugin_delete_item($_POST['delete_item_id']);
}
```

### Corrected Code
```php
if ( isset( $_POST['delete_item_id'] ) ) {
    // 1. Verify CSRF Nonce
    if ( ! isset( $_POST['my_plugin_nonce_field'] ) || ! wp_verify_nonce( sanitize_key( $_POST['my_plugin_nonce_field'] ), 'my_plugin_delete_item' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'my-plugin' ) );
    }

    // 2. Verify Capability
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to perform this action.', 'my-plugin' ) );
    }

    // 3. Sanitize and Process
    $item_id = absint( $_POST['delete_item_id'] );
    my_plugin_delete_item( $item_id );
}
```

---

## 3. Output Escaping (Prevent Cross-Site Scripting)

AIs frequently output values directly without escaping. You must ALWAYS escape output data right before it is rendered to the browser.

### Rules
- Use `esc_html()` for plain text.
- Use `esc_attr()` for HTML attributes.
- Use `esc_url()` for URLs.
- Use `esc_textarea()` for textarea fields.
- Use `wp_kses_post()` or `wp_kses()` if the text is allowed to contain select HTML tags.

### Incorrect AI Code
```php
echo '<input type="text" name="username" value="' . $user_name . '">';
echo '<div class="message">' . $message . '</div>';
```

### Corrected Code
```php
echo '<input type="text" name="username" value="' . esc_attr( $user_name ) . '">';
echo '<div class="message">' . esc_html( $message ) . '</div>';
```

---

## 4. Hook Timing ("Headers Already Sent" Prevention)

AIs often use incorrect hooks, causing issues where headers are sent too early, or scripts are loaded in the wrong lifecycle step.

### Rules
- **Redirects**: `wp_redirect()` or editing cookies/sessions must be done on the `init` or `template_redirect` hooks before any content has output.
- **Scripts & Styles**: Always enqueue styles and scripts on the `wp_enqueue_scripts` (front-end) or `admin_enqueue_scripts` (admin dashboard) hooks, never echo `<link>` or `<script>` tags directly in `wp_head` or body hooks.

### Incorrect AI Code
```php
add_action('wp_head', 'my_plugin_styles');
function my_plugin_styles() {
    echo '<link rel="stylesheet" href="' . plugins_url('style.css', __FILE__) . '">';
}
```

### Corrected Code
```php
add_action( 'wp_enqueue_scripts', 'my_plugin_enqueue_styles' );
function my_plugin_enqueue_styles() {
    wp_enqueue_style( 'my-plugin-style', plugins_url( 'style.css', __FILE__ ), array(), '1.0.0' );
}
```
