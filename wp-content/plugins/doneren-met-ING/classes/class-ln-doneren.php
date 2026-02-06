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
        private $stamp = '
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

        </svg>';



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

            return '<a href="' . esc_url( $donate_url ) . '">' . $this->stamp . '</a>';
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
                error_log('[LN_Doneren] DB INSERT failed: ' . $wpdb->last_error);
                return false;
            }
            return true;
        }

        public function create_payment($amount, $hs_did, $payment_method_id = null, $name = '', $email = '')  {
            global $wpdb;

            // Whitelist hs_did and transactionId to avoid unexpected chars
            if(!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $hs_did)){
                error_log("[LN_Doneren][create_payment] Invalid hs_did: $hs_did");
                return;
            }
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
                error_log('[LN_Doneren] DB UPDATE failed: ' . $wpdb->last_error);
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
            $this->create_payment($amount, $hs_did, $selected_payment_method ?: null, $name, $email);
            return ob_get_clean();
        }

        public function render_donation_form(){
            $hs_did = uniqid('', false);
            $options = get_option('ln_doneren_options', []);

            

            ob_start();
?>
            <div class="ln-donate-form">
                <form action="" method="post" class="ln-donate-form__form">
                    <?php wp_nonce_field('ln_donate_submit', 'ln_donate_nonce'); ?>

                    <input id="hs-did" type="hidden" name="hs_did" value="<?php echo esc_attr($hs_did); ?>">
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
                    <input id="doneer-submit" type="submit" value="Doneer nu" disabled>
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
                    const paymentRadios = form.querySelectorAll('input[name="ln_donate_payment_method"]');
                    const requiresPaymentChoice = paymentRadios.length > 0;


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
                error_log('[LN_Doneren] DB UPDATE failed in set_payment_status: ' . $wpdb->last_error);
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
                error_log('[LN_Doneren] DB UPDATE failed in set_betaaldata: ' . $wpdb->last_error);
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
                    error_log( '[LN_Doneren] Payment successful for order_id: ' . $order_id . ', payment_session_id: ' . $payment_session_id );
                    $updated = $this->set_payment_status($order_id, 'paid');
                    // ga naar pagina donatie - bedankt
                    wp_redirect($page_bedankt);
                    echo( '<p>Bedankt voor uw donatie! Uw betaling is succesvol verwerkt.</p>' );
                    exit;
                    break;
                case -63:
                    error_log( '[LN_Doneren] Payment refused for order_id: ' . $order_id . ', payment_session_id: ' . $payment_session_id );
                    $updated = $this->set_payment_status($order_id, 'refused');
                    wp_redirect($page_cancelled);
                    exit;
                    break;
                case -90:
                    error_log( '[LN_Doneren] Payment cancelled for order_id: ' . $order_id . ', payment_session_id: ' . $payment_session_id );
                    $updated = $this->set_payment_status($order_id, 'cancelled');
                    wp_redirect($page_cancelled);
                    exit;
                    break;
                default:
                    error_log( '[LN_Doneren] Payment status ' . $order_status_id . ' for order_id: ' . $order_id . ', payment_session_id: ' . $payment_session_id );
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
                        error_log( '[LN_Doneren][process_exchange] Exchange processed for transaction ID: ' . $transaction_id );
                        break;
                    case 'pending':
                        $this->set_payment_status($transaction_id, 'pending');
                        break;
                    case 'cancel':
                        $this->set_payment_status($transaction_id, 'cancelled');
                        break;
                    default:
                        error_log( '[LN_Doneren][process_exchange] Unknown action: ' . $action );
                } 
            }
            return '<p>Exchange processed for transaction ID: ' . esc_html($transaction_id) . '</p>';
        }
    }
}
