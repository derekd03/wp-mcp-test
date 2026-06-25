# WordPress MCP and Custom Skills Test Workspace

This workspace is set up to help you evaluate and test how **Google Antigravity** (and other AI agents/desktop clients) work with custom **Skills** and **Model Context Protocol (MCP)** integrations.

---

## 1. Testing the Workspace Skill (Reusable prompts to fix AI errors)

We have created a workspace skill at `.agents/skills/wordpress-helper/SKILL.md`. This skill contains rules instructing the AI to correct common coding errors when working in this project.

### How to run the test:
1. Ensure this directory (`.gemini\antigravity\scratch\wp-mcp-test`) is open as your active workspace in Antigravity.
2. Open the file `my-test-plugin/my-test-plugin-vulnerable.php`. Notice the intentional security and architectural vulnerabilities inside `my_plugin_delete_log()` (SQL injection, XSS, missing nonce checks) and `my_plugin_handle_login_redirect()` (wrong action timing).
3. In the chat, prompt the agent:
   > *"Refactor the code in my-test-plugin-vulnerable.php to fix security and architectural errors."*
4. **Expected Result**: The agent will read the workspace skill (`wordpress-helper`) and output a refactored version matching `my-test-plugin-corrected.php` which implements:
   - `wp_verify_nonce()` for CSRF checks.
   - `current_user_can( 'manage_options' )` to check permissions.
   - `$wpdb->prepare()` to escape parameters.
   - `esc_html()` on the echo output.
   - Move the redirection from `wp_footer` to `template_redirect` or `init`.

---

## 2. Testing the WordPress MCP Server

MCP connects the AI directly to a running WordPress site to perform administrative tasks (e.g. posting articles, uploading media, checking settings).

### Setup Steps:
1. **Prepare WordPress**:
   - Ensure you have a local WordPress site running (via LocalWP, Docker, XAMPP, etc.).
   - Make sure Permalinks are set to **"Post name"** (Settings > Permalinks).
   - Go to Users > Your Profile and scroll down to **Application Passwords**. Add a new password named `Antigravity-Test`, and copy the 24-character password generated.

2. **Configure the Client**:
   - Open your global `mcp_config.json` file. In Antigravity, this is located at:
     `.gemini\antigravity\mcp_config.json`
   - Paste the following server entry (you can copy this from `mcp_config.json.template` in this workspace) and replace the credentials:
     ```json
     {
       "mcpServers": {
         "wordpress-rest": {
           "command": "npx",
           "args": [
             "-y",
             "wordpress-mcp-server"
           ],
           "env": {
             "WORDPRESS_SITE_URL": "http://your-wordpress-site.local",
             "WORDPRESS_USERNAME": "your_username",
             "WORDPRESS_PASSWORD": "xxxx xxxx xxxx xxxx xxxx"
           }
         }
       }
     }
     ```
   - Restart the Antigravity developer environment/client so it loads the new configuration.

3. **Verify Connection**:
   - In the chat, ask the agent:
     > *"Show me a list of my WordPress posts using the MCP server."*
     or
     > *"Create a new draft post titled 'Hello from Antigravity'."*
   - The agent should discover the REST tools and run them to interact with your site.
