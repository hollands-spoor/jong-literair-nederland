<?php


/***
 * 
 * Hier gebruik ik dan de SDK van Pay.nl
 * 
 * Let op: er staat ook een repo 'php-sdk' op hun github. Niet gebruik
 * 
 * https://github.com/paynl/sdk
 * 
 * Zie de readme voor een quickstart
 * 
 * Wellicht een plugin-optie maken voor de betaalmethoden.
 * 
 * 
 * Die vraag je zo allemaal op:
 * require __DIR__ . '/vendor/autoload.php';

 * \Paynl\Config::setTokenCode('AT-####-####');
 * \Paynl\Config::setApiToken('****************************************');
 * \Paynl\Config::setServiceId('SL-####-####');

 * $paymentMethods = \Paynl\Paymentmethods::getList();
 * var_dump($paymentMethods);
 * 
 */

declare(strict_types=1);

/* You might need to adjust this mapping */
require dirname(__DIR__) . '/vendor/autoload.php';


if (! class_exists('LN_Doneren')) {

    class LN_Doneren
    {

        /** @var LN_Doneren|null */
        private static $instance = null;

        private $default_amounts = [5, 10, 25, 50, 100];

        private $username = ''; // Your AT-code (AT-####-####)
        private $password = ''; // Your API Token
        private $serviceId = ''; // Your Sales location code (SL-####-####)
        private $payment_page_id = 0;
        private $return_url = '';
        private $exchange_url = '';
        private $donations_table = '';
        private $hs_did = '';
        private static $method_icons = [
                '10' => 'ideal.png',
                '136' => 'banktransfer.png',
                '436' => 'bancontact.png'
        ];
        private $stamps = ['
        <svg 
            class="steun-ons" viewBox="0 0 100 100" 
            version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"
            style="transform:rotate(15deg);">
            <defs>
                <path id="arc-bottom" d="M 4,50 A 42,42 0 0 0 96,50" fill="none" />
                <path id="arc-top" d="M 13,50 A 37,37 0 0 1 87,50" fill="none" />
            </defs>
            <g id="layer1">
                <circle style="fill:#cc0000;fill-opacity:1;fill-rule:evenodd;stroke:none;" cx="50" cy="50" r="50" />
                <circle style="fill:none;stroke:#fff; stroke-width:0.25px;stroke-linejoin:round" cx="50" cy="50" r="48" />
                <circle style="fill:none;stroke:#fff; stroke-width:0.25px;stroke-linejoin:round" cx="50" cy="50" r="35" />
                <text font-size="0.75em" text-anchor="middle" fill="#fff">
                    <!-- Center with startOffset=50%; dy nudges baseline relative to the path -->
                    <textPath href="#arc-top" startOffset="50%" dy="-0.2em" textLength="60" lengthAdjust="spacing">LITERAIR</textPath>
                    <textPath href="#arc-bottom" startOffset="50%" dy="-1.2em" textLength="90" lengthAdjust="spacing">NEDERLAND
                    </textPath>
                </text>
                <text x="50" y="45" font-size="0.9em" text-anchor="middle" fill="#fff">STEUN</text>
                <text x="50" y="65" font-size="0.9em" text-anchor="middle" fill="#fff">ONS!</text>
            </g>

        </svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 10 120 90"><g style="fill:#13ccb0"><path d="M48.765 16.481c1.024-.18 2.257-.007 2.988.897.406.503.645 1.235.58 2.332-.064 1.073-.095 3.762.212 5.607.372 2.231 1.184 4.372 1.954 6.499.602 1.662 1.027 4.062 2.719 4.418 1.163.244 2.344-1.184 2.846-1.827.873-1.12 1.924-2.534 2.803-3.653.28-.355.735-.908 1.147-.722.484.218.334 1.047.255 1.572-.24 1.609-.63 3.006-.68 4.715-.024.85.088 1.967.807 2.42.677.427 1.64.009 2.38-.297 1.688-.697 3.033-2.071 4.332-3.355 1.49-1.474 2.543-3.337 3.908-4.928.692-.806 1.242-1.812 2.166-2.336.655-.371 1.507-.737 2.21-.467.539.207 1.023.824 1.019 1.402-.006.778-.757 1.391-1.402 1.869-1.06.784-1.653 1.101-3.059 2.506-1.405 1.404-3.93 3.753-5.054 6.16-.452.965-.978 2.147-.595 3.142.185.481.723.851 1.232.935.883.146 1.702-.558 2.548-.85.64-.22 1.237-.715 1.912-.68.392.022.86.174 1.062.51.19.316.168.803-.043 1.105-.657.943-2.48.41-3.185 1.317-.369.474-.474 1.224-.255 1.784.286.73 1.081 1.24 1.826 1.486 2.153.713 4.542-.058 6.797-.297 1.942-.206 4.178-.62 5.777-.977 1.598-.356 2.888-.823 3.823-1.062.934-.239 1.67-.533 2.463-.297.444.132 1.019.432 1.062.892.047.49-.524.873-.934 1.147-.7.467-1.598.544-2.421.722-1.23.265-2.496.312-3.738.51-2.658.422-5.354.71-7.944 1.444-1.199.34-2.496.598-3.483 1.36-.819.63-1.855 1.472-1.827 2.505.017.604.626 1.098 1.147 1.402 1.515.883 3.434.772 5.183.892 1.682.115 3.375-.022 5.055-.17 1.196-.105 2.366-.488 3.568-.51.987-.017 1.844.337 1.911 1.063.07.75-1.024 1.212-1.741 1.444-2.215.716-4.641-.382-6.967-.467-2.207-.082-4.52-.708-6.626-.043-.746.235-1.805.585-1.912 1.36-.166 1.206 1.368 2.053 2.294 2.845 1.788 1.53 4.021 2.455 6.117 3.526 2.46 1.258 4.996 2.371 7.56 3.398 3.555 1.423 7.011 3.012 10.833 3.823 1.117.237 2.462.3 3.228 1.147.352.39.622 1.065.377 1.53-.281.533-1.094.576-1.694.637-.885.088-1.785-.158-2.633-.425-1.8-.567-3.39-1.662-5.098-2.464-3.028-1.422-6.028-2.916-9.132-4.163-3.424-1.375-6.866-2.776-10.45-3.653-1.348-.33-2.769-.909-4.12-.595-.318.074-.652.266-.807.553-.258.472-.213 1.103-.043 1.614.303.908 1.058 1.627 1.784 2.251 1.039.893 2.578 1.092 3.568 2.039.46.44.943.982 1.02 1.614.048.4-.099.861-.383 1.147-.463.467-1.218.74-1.869.637-.89-.14-1.629-.891-2.166-1.614-.86-1.156-1.485-2.404-2.506-3.356-.862-.803-1.797-1.854-2.974-1.911-.588-.029-1.186.361-1.571.807-.43.498-.665 1.211-.637 1.869.084 2.053 1.553 3.808 2.463 5.65.918 1.858 1.904 3.688 3.016 5.437.92 1.445 2.456 3.146 3.016 4.163.633 1.149.971 2.024-.042 2.973-.438.356-1.051.537-1.615.51-.722-.035-1.478-.345-1.996-.85-1.883-1.836-2.088-4.83-3.27-7.179-1.158-2.298-2.09-4.778-3.739-6.754-.832-.996-1.75-2.178-3.016-2.463-.867-.196-1.889.091-2.59.637-.926.719-1.31 1.987-1.658 3.1-.188.606-.273 1.342-.764 1.742-.314.257-.826.434-1.19.255-.705-.346-.814-1.35-1.019-2.124-.214-.805-.39-1.67-.892-2.336-.128-.17-.298-.392-.51-.382-1.312.058-2.137 1.587-2.93 2.633-1.911 2.517-2.914 5.384-3.951 8.368-.76 2.188-.876 4.76-1.7 6.924-.294.777-.601 1.625-1.231 2.167-.69.593-1.647 1.097-2.549.977-.643-.086-1.301-.557-1.572-1.147-.29-.633-.037-1.417.17-2.082.481-1.544 1.72-2.747 2.422-4.205.738-1.535 1.337-3.139 1.869-4.757.686-2.089 1.318-4.207 1.699-6.372.251-1.428.563-2.894.382-4.333-.07-.55-.021-1.408-.552-1.572-.387-.118-.841.341-.977.723-.178.5-.204 1.373-.637 1.869-.48.548-1.227 1.19-1.954 1.217-.597.022-1.379-.207-1.837-.59-1.322-1.337.394-2.207.987-3.346.248-.58.701-1.384.297-1.869-.733-.882-2.313-.296-3.44-.085-2.745.515-7.731 3.228-7.731 3.228s-5.464 2.783-8.156 4.248c-1.259.685-2.36 1.731-3.738 2.124-.709.202-1.562.437-2.209.085-.39-.213-.769-.712-.68-1.147.145-.705 1.054-1.063 1.742-1.274a46.6 46.6 0 0 0 6.754-2.676c2.607-1.29 5.126-2.788 7.476-4.503 1.702-1.242 4.055-2.176 4.758-4.163.22-.623.047-1.418-.34-1.954-.405-.56-1.154-.822-1.827-.977-1.367-.313-2.743-.1-4.205.17-2.881.53-5.832 1.253-8.496 2.251-.704.265-1.556.459-2.25.17-.483-.2-1.084-.67-1.02-1.189.08-.655.971-.995 1.614-1.147 2.86-.677 6.013-.724 8.92-1.53 1.244-.344 2.705-.48 3.61-1.4.488-.496.903-1.284.723-1.955-.253-.942-1.313-1.602-2.251-1.869-1.235-.35-2.317-.319-3.738-.595-.315-.06-.743-.365-.68-.68.066-.33.598-.362.934-.382 1.985-.115 3.862-.046 5.65-.68 1.103-.39 2.762-.717 2.973-1.868.209-1.134-.734-2.208-2.081-2.761-.709-.292-1.664-.394-2.039-1.062-.201-.36-.195-.93.085-1.232.535-.577 1.61-.645 2.336-.34.81.34 1.568.8 2.422.85.768.044 1.82.127 2.25-.51.612-.902-.127-2.208-.509-3.228-.58-1.55-1.346-2.706-2.634-4.206s-5.224-4.502-5.224-4.502-2.388-1.416-3.186-2.507c-.357-.488-.752-1.105-.637-1.699.131-.679.755-1.283 1.401-1.529.785-.299 1.774-.155 2.507.255 1.525.852 2.084 2.806 3.186 4.163.93 1.146 1.788 2.372 2.888 3.356 2.058 1.84 4.23 3.77 6.839 4.672 1.22.422 2.612.567 3.865.255 1.697-.423 3.494-1.304 4.46-2.761 1.07-1.612 1.003-3.803.893-5.735-.116-2.023-.978-3.937-1.615-5.862-.639-1.932-2.047-3.663-2.208-5.692-.049-.606.042-1.28.382-1.784.439-.65.815-.928 1.572-1.062"/><path d="M19.47 42.03c.752-.294 1.969-.48 2.83-.317.557.106 1.133.42 1.445.892.364.55.516 1.333.297 1.954-.325.927-1.257 1.624-2.166 1.997-.786.322-1.748.37-2.549.085-.708-.253-1.429-.784-1.699-1.487-.214-.56-.106-1.277.213-1.784.393-.625.876-1.045 1.628-1.34M59.774 86.921c.637-.763 2.307-1.106 2.796-1.03.766.117 1.587.531 1.996 1.189.376.603.4 1.451.17 2.124-.265.778-.949 1.407-1.656 1.826-.577.341-1.3.61-1.954.467-.784-.171-1.606-.737-1.893-1.486-.374-.977-.129-2.287.541-3.09M110.74 75.016c.51-.248.922-.362 1.657-.382.685-.02 1.446.26 1.911.764.422.458.639 1.168.552 1.784-.122.871-.67 1.79-1.444 2.21-.81.437-1.936.407-2.761 0-.752-.372-1.35-1.178-1.53-1.997-.096-.44.06-.935.298-1.317.298-.479.81-.815 1.317-1.062"/></g><text fill="#fff" font-family="sans-serif" font-size="9" font-weight="bold" text-anchor="middle" transform="rotate(10 70 55)">
      <tspan x="55" y="56">STEUN</tspan>
      <tspan x="54" y="66">ONS!</tspan>
    </text></svg>'
        ];

        



        /**
         * Get (and create) the singleton instance
         *
         * @return LN_Doneren
         */
        public static function instance($page_id)
        {
            global $wpdb;
            if (null === self::$instance) {
                self::$instance = new self();
                self::$instance->init();
            }
            self::$instance->payment_page_id = $page_id;
            self::$instance->return_url = get_permalink($page_id);
            self::$instance->donations_table = $wpdb->prefix . 'ln_donations';
            return self::$instance;
        }


        private function __construct(){

        }
        private function __clone() {}
        public function __wakeup() {}

        public function get_donations_table_name() {
            return $this->donations_table;
        }

        public function render_stamp() {
            $donate_options = get_option('ln_doneren_options');
            $donate_url = get_permalink( $donate_options['donation_page_id'] );
            $stamp = $donate_options['stamp'] ?? '0';

            return '<a href="' . esc_url( $donate_url ) . '">' . $this->stamps[ $stamp ] . '</a>';
        }   

        /**
         * Write plugin-specific log entries to a dedicated uploads file.
         */
        public function log($message): void {
            $upload_dir = wp_upload_dir();
            if (empty($upload_dir['basedir'])) {
                error_log('[LN_Doneren] Upload directory unavailable for logging.');
                return;
            }

            $log_dir = trailingslashit($upload_dir['basedir']) . 'doneren-met-ing';
            if (!is_dir($log_dir) && !wp_mkdir_p($log_dir)) {
                error_log('[LN_Doneren] Unable to create log directory: ' . $log_dir);
                return;
            }

            $log_file = trailingslashit($log_dir) . 'donations.log';
            if (!file_exists($log_file)) {
                $handle = @fopen($log_file, 'a');
                if ($handle === false) {
                    error_log('[LN_Doneren] Unable to create log file: ' . $log_file);
                    return;
                }
                fclose($handle);
            }

            $message_string = is_scalar($message) ? (string) $message : wp_json_encode($message);
            $line = sprintf('[%s] %s%s', current_time('mysql'), $message_string, PHP_EOL);
            $written = file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
            if ($written === false) {
                error_log('[LN_Doneren] Unable to write to log file: ' . $log_file);
            }
        }


        public static function init() {
            
            add_filter( 'ln_donate_payment_method_label', function($label, $method) {
                // Customize labels for specific payment methods if needed
                if (isset($method['visibleName']) && $method[ 'visibleName' ] === 'iDEAL') {
                    $label = 'iDEAL | WERO';
                }
                if( in_array( $method['id'], array_keys(self::$method_icons) ) ) {
                    $icon_url = plugin_dir_url(__DIR__) . 'assets/icons/' . self::$method_icons[ (string) $method['id'] ];
                    $label = '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr($method['visibleName'] ?? $method['name']) . '" style="height:1.2em;vertical-align:middle;margin-right:0.5em;" />' . $label;
                }
                return $label;
            }, 10, 2 );
        }

        public function insert_donation_record($amount, $name, $email, $hs_did) {


            global $wpdb;
            $table_name = $this->donations_table;

            $hs_status = 'new';
            $hs_bedrag = $amount; // Example amount
            $hs_betaalprovider = 'Pay.nl ING-Checkout';
            $hs_betaaldata = json_encode(['test' => 'data']); // Example payment data
            $hs_created_at = current_time('mysql');
            $hs_updated_at = current_time('mysql');
            $hs_naam = $name;
            $hs_email = $email;
            $transaction_id = ''; // Will be updated later
            $sql = $wpdb->prepare(
                "INSERT INTO $table_name (hs_did, hs_status, hs_bedrag, hs_naam, hs_email, hs_betaalprovider, hs_betaaldata, hs_created_at, hs_updated_at, hs_transactieID) VALUES (%s, %s, %f, %s, %s, %s, %s, %s, %s, %s)",
                $hs_did,
                $hs_status,
                $hs_bedrag,
                $hs_naam,
                $hs_email,
                $hs_betaalprovider,
                $hs_betaaldata,
                $hs_created_at,
                $hs_updated_at,
                $transaction_id
            );
            $result = $wpdb->query($sql);
            
            if($result === false){
                $this->log('[LN_Doneren] DB INSERT failed: ' . $wpdb->last_error);
                return false;
            }
            return true;
        }

        public function create_payment($amount, $payment_method_id = null, $name = '', $email = '')  {
            global $wpdb;

            $hs_did = uniqid('', false);

            $options = get_option('ln_doneren_options', []);
            if (!empty($options['token_code'])) {
                \Paynl\Config::setTokenCode($options['token_code']);
            }
            if (!empty($options['api_token'])) {
                \Paynl\Config::setApiToken($options['api_token']);
            }
            if (!empty($options['service_id'])) {
                \Paynl\Config::setServiceId($options['service_id']);
            }
           
            $formatted_amount = number_format($amount, 2, '.', '');
            $selected_payment_method = ($payment_method_id !== null) ? absint($payment_method_id) : 0;
            if ($selected_payment_method <= 0) {
                $selected_payment_method = 10;
            }

            $payment_added = $this->insert_donation_record( $amount, $name, $email, $hs_did );    
            if( !$payment_added) {
                echo '<p>Er is een technische fout opgetreden bij het opslaan van uw donatie.</p>';
                return;
            }

            $transaction_result = \Paynl\Transaction::start(array(
                # Required
                // use $amount
                'amount' => $formatted_amount,

                // use $this->return_url
                'returnUrl' => Paynl\Helper::getBaseUrl() ,
                // Make this a page that is selected in plugin settings
                'exchangeUrl' => Paynl\Helper::getBaseUrl(),

                # Optional
                'currency' => 'EUR',

                // use $this->exchange_url
                // dit is een latere callback / webhook, misschien gebruiken als we handmatige overschrijving toestaan, dan kan je mailtje sturen als overschrijving heeft plaatsgevonden...
                // we doen verder niet aan order-afhandeling, dus eigenlijk hoeft er niets te gebeuren in exchange...

                'paymentMethod' => $selected_payment_method,
                'bank' => 1,
                'description' => 'Literair Nederland donatie',
                // default to '1' 
                'testmode' => ( isset($options['test_mode']) && $options['test_mode'] === '0' ) ? '0' : '1', 

                // Ik wil ergens die unique id meesturen zodat later de status kan worden teruggevonden. Maar ik neem aan de in het $transaction_result van deze transactie ook wel ergens een unique identifier zal staan die meteen na het aanmaken van deze transactie in db kan worden opgeslagen.
                'extra1' => $hs_did,
                'invoiceDate' => new DateTime('now'),
                'enduser' => array(
                    'lastName' => $name,
                    'emailAddress' => $email,
                )
            ));

            # Save this transactionid and link it to your order
            $transactionId = $transaction_result->getTransactionId();

            $updated = $wpdb->update(
            $this->donations_table,
                array(
                    'hs_transactieID' => $transactionId,
                    'hs_status' => 'created',
                    'hs_updated_at' => current_time('mysql'),
                ),
                array('hs_did' => $hs_did),
                array('%s', '%s', '%s'),
                array('%s')
            );

            if($updated === false){
                $this->log('[LN_Doneren] DB UPDATE failed: ' . $wpdb->last_error);
                echo '<p>Er is een technische fout opgetreden bij het opslaan van uw transactie.</p>';
                return;
            }
            # Redirect the customer to this url to complete the payment
            $redirect = $transaction_result->getRedirectUrl();
            echo ('Ga naar de volgende URL om te betalen: <a href="' . esc_url($redirect) . '">' . esc_html($redirect) . '</a>');

            // Allow the external host for wp_safe_redirect (e.g., pay.nl / checkout.pay.nl)
            $host = parse_url($redirect, PHP_URL_HOST);
            if ($host) {
                add_filter('allowed_redirect_hosts', static function ($hosts) use ($host) {
                    $hosts[] = strtolower($host);
                    return array_unique($hosts);
                });
            }

            if (!headers_sent()) {
                // Safe server-side redirect
                wp_safe_redirect(esc_url_raw($redirect), 302);
                exit;
            }
            echo '<p>Ga naar de <a href="' . esc_url($redirect) . '">betaalpagina</a> als u niet automatisch wordt doorgestuurd .</p>';
        }

        public function process_form($atts) {
            // process here all the post vars
            // Only proceed on POST with a valid nonce
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return;
            }
            if (!isset($_POST['ln_donate_nonce']) || !wp_verify_nonce($_POST['ln_donate_nonce'], 'ln_donate_submit')) {
                return '<p>Beveiligingscontrole mislukt. Probeer het opnieuw.</p>';
            }
            ob_start();
            // get name and email
            // get amount
            $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
            $name = isset($_POST['naam']) ? sanitize_text_field($_POST['naam']) : '';
            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $hs_did = isset($_POST['hs_did']) ? sanitize_text_field($_POST['hs_did']) : '';
            if ($amount < 1) {
                return '<p>Ongeldig donatiebedrag.</p>';
            }

            $options = get_option('ln_doneren_options', []);
            $enabled_payment_methods = $this->get_enabled_payment_methods($options);
            $selected_payment_method = '';
            if (!empty($enabled_payment_methods)) {
                $selected_payment_method = isset($_POST['ln_donate_payment_method'])
                    ? sanitize_text_field($_POST['ln_donate_payment_method'])
                    : '';
                if ($selected_payment_method === '' || !array_key_exists($selected_payment_method, $enabled_payment_methods)) {
                    return '<p>Kies een betaalmethode.</p>';
                }
            }

            //$table_name = $wpdb->prefix . 'ln_donations';
            //echo ('naam ' . $name . ' email ' . $email . ' amount ' . $amount);
            //echo (' Return adres: ' . $this->return_url);
            $this->create_payment($amount, $selected_payment_method ?: null, $name, $email);
            return ob_get_clean();
        }

        public function render_donation_form(){
            $options = get_option('ln_doneren_options', []);

            

            ob_start();
?>
            <div class="ln-donate-form">
                <form action="" method="post" class="ln-donate-form__form">
                    <?php wp_nonce_field('ln_donate_submit', 'ln_donate_nonce'); ?>

                    <label for="ln-doneren-naam">Naam (niet verplicht)</label>
                    <input id="ln-doneren-naam" class="ln-input ln-text" type="text" name="naam" />
                    <label for="ln-doneren-email">Email (niet verplicht)</label>
                    <input id="ln-doneren-email" class="ln-input ln-email" type="email" name="email" />
                    <?php
                    $amounts = [];
                    if( isset( $options['donation_amounts'] ) && !empty( $options['donation_amounts'] ) ) {
                        // parse comma-separated values
                        $custom_amounts = array_map('trim', explode(',', $options['donation_amounts']));
                        foreach($custom_amounts as $amt) {
                            $amt_float = floatval($amt);
                            if($amt_float > 0) {
                                $amounts[] = $amt_float;
                            }
                        }
                        if(empty($amounts)) {
                            $amounts = $this->default_amounts;
                        }
                    }
                    foreach ($amounts as $amount) {
                        $formatted_amount = '€' . number_format($amount, 2, ',', '.');
                        echo '<button class="doneren doneren-fixed-amount" type="button" data-amount="' . esc_attr($amount) . '">' . esc_html($formatted_amount) . '</button>';
                    }
                    $custom_allowed = isset($options['donation_allow_custom_amount']) ? (bool)$options['donation_allow_custom_amount'] : false;
                    if ($custom_allowed) :
                    ?>
                    <button class="doneren" id="doneren-custom-amount" type="button">Ander bedrag</button>
                    <div id="other-amount-container" style="display:none;">
                        <label class="label-other-amount" for="other-amount">Ander bedrag:</label>
                        <input type="number" id="other-amount" name="other_amount" min="1" step="any" inputmode="decimal" pattern="[0-9]+([\.,][0-9]{1,2})?">
                    </div>
                    <?php endif; ?>
                    <input id="ln-donate-selected-amount" name="amount" type="hidden" value="">
                    <br>
                    <?php
                    $enabled_payment_methods = $this->get_enabled_payment_methods($options);
                    if (!empty($enabled_payment_methods)) {
                        echo '<fieldset class="ln-donate-payment-methods">';
                        echo '<legend>Kies uw betaalmethode:</legend>';
                        foreach ($enabled_payment_methods as $method_id => $method) {
                            $radio_id = sanitize_html_class('ln-donate-payment-method-' . $method_id);
                            $label = esc_html($method['visibleName'] ?? ($method['name'] ?? __('Onbekende methode', 'doneren-met-ing')));
                            $label = apply_filters( 'ln_donate_payment_method_label', $label, $method);
                            $tech = isset($method['name']) ? esc_html($method['name']) : '';
                            $meta = $tech ? '<span class="ln-donate-payment-option__description">' . $tech . '</span>' : '';
                            printf(
                                '<label class="ln-donate-payment-option" for="%1$s"><input type="radio" id="%1$s" name="ln_donate_payment_method" value="%2$s" /> <span class="ln-donate-payment-option__label">%3$s</span>%4$s</label>',
                                esc_attr($radio_id),
                                esc_attr((string) $method_id),
                                $label,
                                $meta
                            );
                        }
                        echo '</fieldset>';
                    } else {
                        echo '<p class="description">Er zijn geen betaalmethoden beschikbaar. Controleer de plugin-instellingen.</p>';
                    }

                    ?>
                    <div class="ln-submit-area">
                        <input id="doneer-submit" type="submit" value="Doneer nu" disabled aria-disabled="true">
                        <p id="warning-message" class="ln-warning-message" role="alert" aria-live="polite">Kies bedrag en betaalwijze.</p>
                    </div>
                </form>
            </div>

            <style>

                .ln-donate-form__form {
                    max-width: 30em;
                }

                .ln-input {
                    width: 100%;
                    margin: 0.5em 0;
                    padding: 0.5em 1em;
                    font-size: 1em;
                    border: 1px solid #000000;
                    border-radius: 4px;
                    box-sizing: border-box;
                }

                button.doneren,
                #doneer-submit {
                    margin: 0.5em 0;
                    font-size: 1em;
                    cursor: pointer;
                    width: 100%;
                    background-color: #000000;
                    color: #fff;
                    border: none;
                    border-radius: 4px;
                }

                button.doneren {
                    padding: 0.5em 1em;
                }

                #doneer-submit {
                    padding: 1em 1em;
                }

                button.doneren.selected,
                #doneer-submit.selected {
                    background-color: #cc0000;
                }

                .label-other-amount {
                    display: block;
                    margin: 1em 0 0.5em 0
                }

                #other-amount {
                    width: 100%;
                    margin: 0.5em 0;
                    padding: 0.5em 1em;
                    font-size: 1em;
                    box-sizing: border-box;
                }

                #doneer-submit[disabled] {
                    opacity: 0.6;
                    cursor: not-allowed;
                }


                #doneer-submit:not([disabled]):hover {
                    background-color: #cc0000;
                    /* cursor stays pointer from the base rule */
                }

                #doneer-submit:not([disabled]):focus-visible {
                    outline: 2px solid #cc0000;
                    outline-offset: 2px;
                }

                .ln-donate-payment-methods {
                    border: 1px solid #000000;
                    border-radius: 4px;
                    padding: 1em;
                    margin: 1em 0;
                }

                .ln-donate-payment-methods legend {
                    font-weight: 600;
                    margin-bottom: 0.5em;
                }

                .ln-donate-payment-option {
                    display: flex;
                    align-items: center;
                    gap: 0.5em;
                    margin-bottom: 0.4em;
                    font-size: 0.95em;
                }

                .ln-donate-payment-option__label {
                    font-weight: 600;
                }

                .ln-donate-payment-option__description {
                    margin-left: auto;
                    font-size: 0.85em;
                    opacity: 0.7;
                }

                .ln-submit-area {
                    margin-top: 1em;
                }

                .ln-warning-message {
                    color: #cc0000;
                    font-weight: 600;
                    display: none;
                    margin: 0.5em 0 0;
                }

                .ln-warning-message.is-visible {
                    display: block;
                }
            </style>
            <script>
                (function() {
                    const form = document.querySelector('.ln-donate-form__form');
                    if (!form) return;
                    const fixedButtons = form.querySelectorAll('.doneren-fixed-amount');
                    const customBtn = document.getElementById('doneren-custom-amount');
                    const otherWrap = document.getElementById('other-amount-container');
                    const otherInput = document.getElementById('other-amount');
                    const hasCustom = !!(customBtn && otherWrap && otherInput);
                    const hiddenAmount = document.getElementById('ln-donate-selected-amount');
                    const submitBtn = document.getElementById('doneer-submit');
                    const submitArea = form.querySelector('.ln-submit-area');
                    const warningMessage = document.getElementById('warning-message');
                    const warningText = 'Kies bedrag en betaalwijze';
                    const paymentRadios = form.querySelectorAll('input[name="ln_donate_payment_method"]');
                    const requiresPaymentChoice = paymentRadios.length > 0;

                    function showWarning() {
                        if (!warningMessage) return;
                        warningMessage.textContent = warningText;
                        warningMessage.classList.add('is-visible');
                    }

                    function hideWarning() {
                        if (!warningMessage) return;
                        warningMessage.classList.remove('is-visible');
                    }


                    function clearSelected() {
                        fixedButtons.forEach(b => b.classList.remove('selected'));
                        if (customBtn) {
                            customBtn.classList.remove('selected');
                        }
                    }

                    function normalizeAmount(v) {
                        return (v || '').toString().replace(',', '.').trim();
                    }

                    function hasValidAmount() {
                        const v = normalizeAmount(hiddenAmount.value);
                        const n = parseFloat(v);
                        return v !== '' && !isNaN(n) && n > 0;
                    }

                    function hasSelectedPaymentMethod() {
                        if (!requiresPaymentChoice) {
                            return true;
                        }
                        return Array.from(paymentRadios).some(radio => radio.checked);
                    }


                    function updateSubmitState() {
                        const valid = hasValidAmount() && hasSelectedPaymentMethod();
                        submitBtn.disabled = !valid;
                        submitBtn.setAttribute('aria-disabled', String(!valid));
                        if (valid) {
                            hideWarning();
                        }
                        //submitBtn.classList.toggle('selected', valid);
                    }



                    function setSubmit() {
                        // check if one of the fixed buttons is selected or custom amount filled
                        const isFixedSelected = Array.from(fixedButtons).some(b => b.classList.contains('selected'));
                        const hasCustomValue = hasCustom && otherWrap.style.display !== 'none' && otherInput.value;

                        hiddenAmount.value = isFixedSelected ? hiddenAmount.value : hasCustomValue ? otherInput.value.replace(',', '.') : '';
                        if (hiddenAmount.value) {
                            document.getElementById('doneer-submit').classList.add('selected');
                        }
                    }

                    fixedButtons.forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            clearSelected();
                            this.classList.add('selected');
                            // hide custom
                            if (hasCustom) {
                                otherWrap.style.display = 'none';
                                otherInput.value = '';
                            }
                            hiddenAmount.value = this.getAttribute('data-amount');
                            updateSubmitState();
                        });
                    });

                    if (requiresPaymentChoice) {
                        paymentRadios.forEach(radio => {
                            radio.addEventListener('change', updateSubmitState);
                        });
                    }

                    if (hasCustom) {
                        customBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            clearSelected();
                            this.classList.add('selected');
                            const show = otherWrap.style.display === 'none';
                            otherWrap.style.display = show ? 'block' : 'none';
                            if (show) {
                                otherInput.focus();
                                hiddenAmount.value = '';
                            } else {
                                otherInput.value = '';
                            }
                            updateSubmitState();
                        });

                        otherInput.addEventListener('input', function() {
                            hiddenAmount.value = normalizeAmount(this.value);
                            updateSubmitState();
                        });
                        otherInput.addEventListener('change', function() {
                            hiddenAmount.value = normalizeAmount(this.value);
                            updateSubmitState();
                        });
                    }

                    if (submitArea) {
                        const triggerWarning = function(event) {
                            if (!submitBtn.disabled) {
                                hideWarning();
                                return;
                            }
                            if (event && typeof event.preventDefault === 'function') {
                                event.preventDefault();
                            }
                            showWarning();
                        };

                        submitArea.addEventListener('mouseenter', triggerWarning);
                        submitArea.addEventListener('click', triggerWarning);
                        submitArea.addEventListener('touchstart', triggerWarning, { passive: true });
                    }

                    // On submit, if custom amount visible, copy its value
                    form.addEventListener('submit', function(e) {
                        if (hasCustom && otherWrap.style.display !== 'none' && otherInput.value) {
                            hiddenAmount.value = otherInput.value.replace(',', '.');
                        }
                        if (!hiddenAmount.value) {
                            e.preventDefault();
                            alert('Kies of vul een bedrag in.');
                            return;
                        }
                        if (!hasSelectedPaymentMethod()) {
                            e.preventDefault();
                            alert('Kies een betaalmethode.');
                            return;
                        }
                    });
                    updateSubmitState();
                })();
            </script>
<?php
            return ob_get_clean();
        }

        private function get_enabled_payment_methods(array $options): array {
            if (empty($options['payment_methods']) || !is_array($options['payment_methods'])) {
                return [];
            }

            return array_filter(
                $options['payment_methods'],
                static function ($method) {
                    return !isset($method['enabled']) || $method['enabled'] === '1';
                }
            );
        }

        private function set_payment_status($transactionId, $status){
            global $wpdb;
            $table_name = $this->donations_table;
            $updated = $wpdb->update(
                $table_name,
                array(
                    'hs_status' => $status
                ),
                array( 'hs_transactieID' => $transactionId ),
                array( '%s' ),
                array( '%s' )
            );

            if($updated === false){
                $this->log('[LN_Doneren] DB UPDATE failed in set_payment_status: ' . $wpdb->last_error);
            }

            return $updated;
        }

        private function set_betaaldata($hs_did, $betaaldata){
            global $wpdb;
            $table_name = $this->donations_table;
            $updated = $wpdb->update(
                $table_name,
                array(
                    'hs_betaaldata' => $betaaldata
                ),
                array( 'hs_did' => $hs_did ),
                array( '%s' ),
                array( '%s' )
            );

            if($updated === false){
                $this->log('[LN_Doneren] DB UPDATE failed in set_betaaldata: ' . $wpdb->last_error);
            }

            return $updated;
        }

        private function get_transaction_by_id ($transactionId){
            global $wpdb;
            $table_name = $this->donations_table;
            $result = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM $table_name WHERE hs_transactieID = %s",
                $transactionId
            ) );
            return $result;
        }

        public function redirect_after_payment( $order_id, $order_status_id, $payment_session_id ) {
            // Process payment here
            // You can use the $order_id, $order_status_id, and $payment_session_id parameters as needed
            // For example, you might want to update the order status based on the payment result

            // $table_name = $wpdb->prefix . 'ln_donations';

             $options = get_option('ln_doneren_options', []);
             $page_bedankt = isset($options['donation_bedankt']) ? get_permalink($options['donation_bedankt']) : '';    
             $page_cancelled = isset($options['donation_cancelled']) ? get_permalink($options['donation_cancelled']) : '';    
             $page_pending = isset($options['donation_pending']) ? get_permalink($options['donation_pending']) : '';    
             $page_failed = isset($options['donation_failed']) ? get_permalink($options['donation_failed']) : '';    
            
            /**
             * Kies één van de volgende simulatie opties
             * Betaald100 
            * Betaling is geslaagd  

            *In betaalomgeving50 
            *Betaling is nog niet afgerond en kan nog betaald worden  
            *
            *Geautoriseerd95 
            *Een betaling in de status "reservering" zetten. Vervolgactie VOID / CAPTURE. 
            *
            *Verifiëren85 
            *Een betaling als "afwijkend" markeren. Vervolgactie APPROVE / DECLINE. 
            *
            *Annuleren-90
            *
            *Betaling is door de gebruiker geannuleerd  
            *
            *Weigeren-63 Betaling geweigerd 
            */
            switch ( $order_status_id ) {   
                case 100:
                    $this->log( '[LN_Doneren] Payment successful for order_id: ' . $order_id . ', payment_session_id: ' . $payment_session_id );
                    $updated = $this->set_payment_status($order_id, 'paid');
                    // ga naar pagina donatie - bedankt
                    wp_redirect($page_bedankt);
                    echo( '<p>Bedankt voor uw donatie! Uw betaling is succesvol verwerkt.</p>' );
                    exit;
                    break;
                case -63:
                    $this->log( '[LN_Doneren] Payment refused for order_id: ' . $order_id . ', payment_session_id: ' . $payment_session_id );
                    $updated = $this->set_payment_status($order_id, 'refused');
                    wp_redirect($page_cancelled);
                    exit;
                    break;
                case -90:
                    $this->log( '[LN_Doneren] Payment cancelled for order_id: ' . $order_id . ', payment_session_id: ' . $payment_session_id );
                    $updated = $this->set_payment_status($order_id, 'cancelled');
                    wp_redirect($page_cancelled);
                    exit;
                    break;
                default:
                    $this->log( '[LN_Doneren] Payment status ' . $order_status_id . ' for order_id: ' . $order_id . ', payment_session_id: ' . $payment_session_id );
                    // andere statussen kunnen hier worden afgehandeld indien nodig
                    $updated = $this->set_payment_status($order_id, 'pending');
                    // ga naar pagina donatie - onbekende afloop
                    wp_redirect($page_pending);
                    exit;
            }



        }

        public function process_exchange( $action, $payment_session_id, $transaction_id ) {
            // Process exchange here
            // You can use the $payment_session_id and $order_id parameters as needed
            // See: https://docs.pay.nl/developers#exchange-parameters

            // Note: Implement your exchange logic here as needed
            $transaction = $this->get_transaction_by_id($transaction_id);
            if( $transaction ) {
                $this->set_betaaldata( $transaction->hs_did, json_encode( $_GET ) );

                switch( $action) {
                    case 'new_ppt':
                    // Update payment status based on exchange data
                        $this->set_payment_status($transaction_id, 'paid');        
                        $this->log( '[LN_Doneren][process_exchange] Exchange processed for transaction ID: ' . $transaction_id );
                        break;
                    case 'pending':
                        $this->set_payment_status($transaction_id, 'pending');
                        break;
                    case 'cancel':
                        $this->set_payment_status($transaction_id, 'cancelled');
                        break;
                    default:
                        $this->log( '[LN_Doneren][process_exchange] Unknown action: ' . $action );
                } 
            }
            return '<p>Exchange processed for transaction ID: ' . esc_html($transaction_id) . '</p>';
        }
    }
}
