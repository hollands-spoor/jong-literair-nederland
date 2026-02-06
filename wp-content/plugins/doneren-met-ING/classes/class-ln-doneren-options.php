<?php
// options page for donatie

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('LN_Doneren_Options')) {

    class LN_Doneren_Options {

        /** @var LN_Doneren_Options|null */
        private static $instance = null;

        /** @var array<int|string, mixed> */
        private $payment_methods = [];

        /**
         * Get singleton instance
         */
        public static function instance(): LN_Doneren_Options {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Private constructor to enforce singleton
         */
        private function __construct() {
            add_action('admin_menu', array($this, 'add_plugin_page'));
            add_action('admin_init', array($this, 'page_init'));
        }

        /**
         * Prevent cloning
         */
        private function __clone() {}

        /**
         * Prevent unserializing
         */
        public function __wakeup() {
            // Do nothing; enforce singleton
        }

        public function add_plugin_page() {
            add_options_page(
                'LN Doneren Instellingen',
                'LN Doneren',
                'manage_options',
                'ln-doneren-settings',
                array($this, 'create_admin_page')
            );
        }

        public function create_admin_page() {
            ?>
            <div class="wrap">
                <h1>LN Doneren Instellingen</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('ln_doneren_option_group');
                    do_settings_sections('ln-doneren-settings-admin');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        public function page_init() {
            register_setting(
                'ln_doneren_option_group',
                'ln_doneren_options',
                array($this, 'sanitize')
            );

            add_settings_section(
                'pages_section_id',
                'Pagina Instellingen',
                null,
                'ln-doneren-settings-admin'
            );

            add_settings_field(
                'donation_page_id',
                'Donatie Pagina',
                array($this, 'donation_page_id_callback'),
                'ln-doneren-settings-admin',
                'pages_section_id'
            );
                        // Extra pagina select velden
            add_settings_field(
                'donation_general',
                'Donatie Algemene Informatie',
                array($this, 'donation_general_callback'),
                'ln-doneren-settings-admin',
                'pages_section_id'
            );
            add_settings_field(
                'donation_bedankt',
                'Bedankt Pagina',
                array($this, 'donation_bedankt_callback'),
                'ln-doneren-settings-admin',
                'pages_section_id'
            );
            add_settings_field(
                'donation_cancelled',
                'Geannuleerd Pagina',
                array($this, 'donation_cancelled_callback'),
                'ln-doneren-settings-admin',
                'pages_section_id'
            );
            add_settings_field(
                'donation_pending',
                'In Behandeling Pagina',
                array($this, 'donation_pending_callback'),
                'ln-doneren-settings-admin',
                'pages_section_id'
            );
            add_settings_field(
                'donation_failed',
                'Mislukt Pagina',
                array($this, 'donation_failed_callback'),
                'ln-doneren-settings-admin',
                'pages_section_id'
            );

            add_settings_section(
                'amount_section_id',
                'Bedrag Instellingen',
                null,
                'ln-doneren-settings-admin'
            );


            add_settings_field(
                'default_amount',
                'Standaard Donatiebedrag',
                array($this, 'default_amount_callback'),
                'ln-doneren-settings-admin',
                'amount_section_id'
            );
            add_settings_field(
                'donation_amounts',
                'Donatiebedragen (komma gescheiden)',
                array($this, 'donation_amounts_callback'),
                'ln-doneren-settings-admin',
                'amount_section_id'
            );
            add_settings_field(
                'donation_allow_custom_amount',
                'Vrij bedrag toestaan',
                array($this, 'donation_allow_custom_amount_callback'),
                'ln-doneren-settings-admin',
                'amount_section_id'
            );

            add_settings_section(
                'payment_methods_section_id',
                'Betaalmethoden',
                null,
                'ln-doneren-settings-admin'
            );

            $this->payment_methods = $this->get_payment_methods(true);

            add_settings_field(
                'payment_methods',
                'Beschikbare betaalmethoden',
                array($this, 'payment_methods_callback'),
                'ln-doneren-settings-admin',
                'payment_methods_section_id'
            );


            add_settings_section(
                'pay_nl_section_id',
                'Pay.nl Instellingen',
                null,
                'ln-doneren-settings-admin'
            );


            add_settings_field(
                'token_code',
                'Pay.nl Token Code (AT-…)',
                array($this, 'token_code_callback'),
                'ln-doneren-settings-admin',
                'pay_nl_section_id'
            );
            add_settings_field(
                'api_token',
                'Pay.nl API Token',
                array($this, 'api_token_callback'),
                'ln-doneren-settings-admin',
                'pay_nl_section_id'
            );
            add_settings_field(
                'service_id',
                'Pay.nl Service ID (SL-…)',
                array($this, 'service_id_callback'),
                'ln-doneren-settings-admin',
                'pay_nl_section_id'
            );
            add_settings_field(
                'test_mode',
                'Testmodus',
                array($this, 'test_mode_callback'),
                'ln-doneren-settings-admin',
                'pay_nl_section_id'
            );


        }

        public function sanitize($input) {
            $sanitized = array();
            if (isset($input['paypal_email'])) {
                $sanitized['paypal_email'] = sanitize_email($input['paypal_email']);
            }
            if (isset($input['default_amount'])) {
                $sanitized['default_amount'] = absint($input['default_amount']);
            }
            if (isset($input['donation_amounts'])) {
                $raw = sanitize_text_field($input['donation_amounts']);
                $parts = array_filter(array_map('trim', explode(',', $raw)), 'strlen');
                $sanitized['donation_amounts'] = implode(',', $parts);
            }
            if (isset($input['donation_allow_custom_amount'])) {
                $sanitized['donation_allow_custom_amount'] = $input['donation_allow_custom_amount'] === '1' ? '1' : '0';
            }
            if (isset($input['donation_page_id'])) {
                $sanitized['donation_page_id'] = absint($input['donation_page_id']);
            }
        // Extra pagina ids
            if (isset($input['donation_bedankt'])) {
                $sanitized['donation_bedankt'] = absint($input['donation_bedankt']);
            }
            if (isset($input['donation_general'])) {
                $sanitized['donation_general'] = absint($input['donation_general']);
            }
            if (isset($input['donation_cancelled'])) {
                $sanitized['donation_cancelled'] = absint($input['donation_cancelled']);
            }
            if (isset($input['donation_pending'])) {
                $sanitized['donation_pending'] = absint($input['donation_pending']);
            }
            if (isset($input['donation_failed'])) {
                $sanitized['donation_failed'] = absint($input['donation_failed']);
            }

            if (isset($input['token_code'])) {
                $raw = strtoupper(trim($input['token_code']));
                $sanitized['token_code'] = preg_match('/^AT-\d{4}-\d{4}$/', $raw) ? $raw : '';
            }
            if (isset($input['api_token'])) {
                // Allow hex-ish or mixed tokens; strip whitespace
                $sanitized['api_token'] = preg_replace('/[^A-Za-z0-9]/', '', trim($input['api_token']));
            }
            if (isset($input['service_id'])) {
                $raw = strtoupper(trim($input['service_id']));
                $sanitized['service_id'] = preg_match('/^SL-\d{4}-\d{4}$/', $raw) ? $raw : '';
            }
            if (isset($input['test_mode'])) {
                $sanitized['test_mode'] = $input['test_mode'] === '1' ? '1' : '0';
            }

            $payment_method_input = isset($input['payment_methods']) && is_array($input['payment_methods'])
                ? $input['payment_methods']
                : [];
            $credential_overrides = array(
                'token_code' => $sanitized['token_code'] ?? ($input['token_code'] ?? null),
                'api_token' => $sanitized['api_token'] ?? ($input['api_token'] ?? null),
                'service_id' => $sanitized['service_id'] ?? ($input['service_id'] ?? null),
            );
            $sanitized['payment_methods'] = $this->sanitize_payment_methods_input($payment_method_input, $credential_overrides);

            return $sanitized;
        }

        // ...existing code...
        private function render_page_select($field_key, $placeholder){
            $options = get_option('ln_doneren_options');
            $selected = isset($options[$field_key]) ? (int)$options[$field_key] : 0;
            $pages = get_pages(['post_status' => 'publish']);
            echo '<select name="ln_doneren_options['.esc_attr($field_key).']">';
            echo '<option value="0">'.esc_html($placeholder).'</option>';
            foreach($pages as $page){
                printf(
                    '<option value="%d"%s>%s</option>',
                    $page->ID,
                    selected($selected, $page->ID, false),
                    esc_html($page->post_title)
                );
            }
            echo '</select>';
        }


        public function paypal_email_callback() {
            $options = get_option('ln_doneren_options');
            ?>
            <input type="email"
                   name="ln_doneren_options[paypal_email]"
                   value="<?php echo isset($options['paypal_email']) ? esc_attr($options['paypal_email']) : ''; ?>" />
            <?php
        }

        public function default_amount_callback() {
            $options = get_option('ln_doneren_options');
            ?>
            <input type="number"
                   name="ln_doneren_options[default_amount]"
                   value="<?php echo isset($options['default_amount']) ? esc_attr($options['default_amount']) : ''; ?>" />
            <?php
        }
        public function donation_amounts_callback(){
            $options = get_option('ln_doneren_options');
            $value = isset($options['donation_amounts']) ? esc_attr($options['donation_amounts']) : '';
            ?>
            <input type="text"
                   name="ln_doneren_options[donation_amounts]"
                   value="<?php echo $value; ?>"
                   placeholder="10,15,25,50" />
            <p class="description">Voer vaste bedragen in, gescheiden door komma’s. Laat leeg om deze optie niet te gebruiken.</p>
            <?php
        }
        public function donation_allow_custom_amount_callback(){
            $options = get_option('ln_doneren_options');
            $is_allowed = isset($options['donation_allow_custom_amount']) ? $options['donation_allow_custom_amount'] === '1' : false;
            ?>
            <label>
                <input type="hidden" name="ln_doneren_options[donation_allow_custom_amount]" value="0" />
                <input type="checkbox" name="ln_doneren_options[donation_allow_custom_amount]" value="1" <?php checked($is_allowed); ?> />
                <?php esc_html_e('Sta donateurs toe zelf een bedrag te kiezen', 'good-qr-block'); ?>
            </label>
            <p class="description">Ingeschakeld betekent dat gebruikers buiten de voorgestelde bedragen om een vrij bedrag mogen invullen.</p>
            <?php
        }
        public function donation_page_id_callback(){
            $options = get_option('ln_doneren_options');
            $selected = isset($options['donation_page_id']) ? (int)$options['donation_page_id'] : 0;

            // Find pages with the shortcode
            $shortcode_pages = [];
            $all_pages = get_pages(['post_status' => 'publish']);
            foreach($all_pages as $p){
                if(strpos($p->post_content, '[ln_donate]') !== false){
                    $shortcode_pages[] = $p;
                }
            }

            $list = !empty($shortcode_pages) ? $shortcode_pages : $all_pages;

            echo '<select name="ln_doneren_options[donation_page_id]">';
            echo '<option value="0">' . esc_html__('-- Kies een pagina --', 'good-qr-block') . '</option>';
            foreach($list as $page){
                printf(
                    '<option value="%d"%s>%s%s</option>',
                    $page->ID,
                    selected($selected, $page->ID, false),
                    esc_html($page->post_title),
                    (strpos($page->post_content, '[ln_donate]') !== false ? ' (shortcode)' : '')
                );
            }
            echo '</select>';

            if(empty($shortcode_pages)){
                echo '<p class="description">'
                . esc_html__('Geen pagina met [ln_donate] gevonden; alle pagina’s getoond.', 'good-qr-block')
                . '</p>';
            } else {
                echo '<p class="description">'
                . esc_html__('Alleen pagina’s met de shortcode plus aanduiding “(shortcode)”.', 'good-qr-block')
                . '</p>';
            }
        }

        public function donation_general_callback(){
            $this->render_page_select('donation_general', '-- Kies algemene informatie pagina --');
        }
        public function donation_bedankt_callback(){
            $this->render_page_select('donation_bedankt', '-- Kies bedankt pagina --');
        }
        public function donation_cancelled_callback(){
            $this->render_page_select('donation_cancelled', '-- Kies geannuleerd pagina --');
        }
        public function donation_pending_callback(){
            $this->render_page_select('donation_pending', '-- Kies in behandeling pagina --');
        }
        public function donation_failed_callback(){
            $this->render_page_select('donation_failed', '-- Kies mislukt pagina --');
        }

        public function payment_methods_callback(){
            $available_methods = $this->get_payment_methods();
            if (empty($available_methods)) {
                echo '<p class="description">Betaalmethoden kunnen niet worden opgehaald. Controleer of de Pay.nl API-gegevens correct zijn opgeslagen en probeer het opnieuw.</p>';
                return;
            }

            $options = get_option('ln_doneren_options');
            $selected = isset($options['payment_methods']) && is_array($options['payment_methods'])
                ? $options['payment_methods']
                : [];

            echo '<fieldset class="ln-doneren-payment-methods">';
            foreach ($available_methods as $method_id => $method_data) {
                $method_array = $this->convert_payment_method_to_array($method_data);
                $label = $method_array['visibleName'] ?? ($method_array['name'] ?? sprintf(__('Methode %s', 'doneren-met-ing'), $method_id));
                $technical = $method_array['name'] ?? '';
                $is_enabled = isset($selected[$method_id]['enabled'])
                    ? $selected[$method_id]['enabled'] === '1'
                    : true;

                $description = $technical
                    ? sprintf('#%s · %s', $method_id, $technical)
                    : sprintf('#%s', $method_id);

                printf(
                    '<label style="display:block;margin-bottom:4px;"><input type="hidden" name="ln_doneren_options[payment_methods][%1$s][enabled]" value="0" /><input type="checkbox" name="ln_doneren_options[payment_methods][%1$s][enabled]" value="1" %2$s /> <strong>%3$s</strong> <span class="description" style="opacity:.7;">%4$s</span></label>',
                    esc_attr((string) $method_id),
                    checked($is_enabled, true, false),
                    esc_html($label),
                    esc_html($description)
                );
            }
            echo '</fieldset>';
        }

        public function token_code_callback(){
            $options = get_option('ln_doneren_options');
            $value = isset($options['token_code']) ? esc_attr($options['token_code']) : '';
            echo '<input type="text" name="ln_doneren_options[token_code]" value="'.$value.'" placeholder="AT-1234-5678" />';
            echo '<p class="description">Formaat: AT-####-####</p>';
        }

        public function api_token_callback(){
            $options = get_option('ln_doneren_options');
            $value = isset($options['api_token']) ? esc_attr($options['api_token']) : '';
            echo '<input type="password" name="ln_doneren_options[api_token]" value="'.$value.'" autocomplete="new-password" />';
            echo '<p class="description">Wordt gebruikt voor API calls; opgeslagen als platte tekst.</p>';
        }

        public function service_id_callback(){
            $options = get_option('ln_doneren_options');
            $value = isset($options['service_id']) ? esc_attr($options['service_id']) : '';
            echo '<input type="text" name="ln_doneren_options[service_id]" value="'.$value.'" placeholder="SL-1234-5678" />';
            echo '<p class="description">Formaat: SL-####-####</p>';
        }

        public function test_mode_callback(){
            $options = get_option('ln_doneren_options');
            $is_enabled = isset($options['test_mode']) ? $options['test_mode'] === '1' : false;
            ?>
            <label>
                <input type="hidden" name="ln_doneren_options[test_mode]" value="0" />
                <input type="checkbox" name="ln_doneren_options[test_mode]" value="1" <?php checked($is_enabled); ?> />
                <?php esc_html_e('Testmodus inschakelen', 'good-qr-block'); ?>
            </label>
            <p class="description">Activeer testtransacties voor Pay.nl zonder echte betalingen te starten.</p>
            <?php
        }

        private function sanitize_payment_methods_input(array $submitted_methods, array $credential_overrides = []): array {
            $api_methods = $this->fetch_payment_methods_from_api($credential_overrides);
            if (empty($api_methods)) {
                $existing = get_option('ln_doneren_options', []);
                return isset($existing['payment_methods']) && is_array($existing['payment_methods'])
                    ? $existing['payment_methods']
                    : [];
            }

            $result = [];
            foreach ($api_methods as $method_id => $method_data) {
                $method_array = $this->convert_payment_method_to_array($method_data);
                $enabled_flag = $submitted_methods[$method_id]['enabled'] ?? '0';
                $method_array['enabled'] = $this->sanitize_checkbox_value($enabled_flag);
                $result[$method_id] = $method_array;
            }

            return $result;
        }

        private function sanitize_checkbox_value($value): string {
            if (is_array($value)) {
                $value = reset($value);
            }
            return ($value === '1' || $value === 1 || $value === true || $value === 'on') ? '1' : '0';
        }

        private function convert_payment_method_to_array($method) {
            if (is_array($method)) {
                $converted = [];
                foreach ($method as $key => $value) {
                    $converted[$key] = $this->convert_payment_method_to_array($value);
                }
                return $converted;
            }

            if (is_object($method)) {
                if (method_exists($method, 'toArray')) {
                    return $this->convert_payment_method_to_array($method->toArray());
                }
                if ($method instanceof \JsonSerializable) {
                    return $this->convert_payment_method_to_array($method->jsonSerialize());
                }
                return $this->convert_payment_method_to_array(get_object_vars($method));
            }

            return $method;
        }

        private function get_payment_methods(bool $force_refresh = false): array {
            if ($force_refresh || empty($this->payment_methods)) {
                $this->payment_methods = $this->fetch_payment_methods_from_api();
            }
            return $this->payment_methods;
        }

        private function fetch_payment_methods_from_api(array $overrides = []): array {
            $options = get_option('ln_doneren_options', []);
            $merged_overrides = array_filter(
                $overrides,
                static function ($value) {
                    return $value !== null;
                }
            );
            $credentials = array_merge($options, $merged_overrides);

            if (empty($credentials['token_code']) || empty($credentials['api_token']) || empty($credentials['service_id'])) {
                return [];
            }

            try {
                \Paynl\Config::setTokenCode($credentials['token_code']);
                \Paynl\Config::setApiToken($credentials['api_token']);
                \Paynl\Config::setServiceId($credentials['service_id']);

                $methods = \Paynl\Paymentmethods::getList([]);

                if ($methods instanceof \Traversable) {
                    $methods = iterator_to_array($methods);
                } elseif (is_object($methods) && method_exists($methods, 'toArray')) {
                    $methods = $methods->toArray();
                } elseif (!is_array($methods)) {
                    $methods = [];
                }
            } catch (\Throwable $exception) {
                error_log('LN Doneren: kan betaalmethoden niet ophalen: ' . $exception->getMessage());
                $methods = [];
            }

            return $methods;
        }
    }

    // Bootstrap the singleton once (e.g. plugin load)
    
}