## TODO: 

Make fetching source an option:

# source: 
    easycbapi.nl/isbn/ < now in ln-bibliographics-rest.php < that is not a good spot, there this sites own restroutes are defined. Move it to a new ln-bibliographics-fetch-by-isbn.php

    https://titelbank.hollands-spoor.com/wp-json/titelbank-api/v1/titel/

    DB  < When we install the titelbank plugin in this website, it can have its own internal wp_titels table for quick checking. Make this default and go fetch in an api when isbn is not found. DB Has no images, maybe secretly import images from cb on editing?





## make a call to the ISBN api on titelbank.hollands-spoor.com:

```php
/**
 * Fetch titelbank ISBN data from staging API.
 */
function mysite_get_titelbank_isbn( string $isbn ) {
    $base_url = 'https://titelbank.hollands-spoor.com/wp-json/titelbank-api/v1/titel/';
    $username = 'admin';
    $password = 'sdqPg77z9bQfaTJhFkrPLgaN';

    $url = $base_url . rawurlencode( preg_replace( '/[^0-9Xx-]/', '', $isbn ) );

    $response = wp_remote_get(
        $url,
        [
            'timeout' => 15,
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
            ],
        ]
    );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'titelbank_http_error', $response->get_error_message() );
    }

    $status = wp_remote_retrieve_response_code( $response );
    $body   = wp_remote_retrieve_body( $response );
    $data   = json_decode( $body, true );

    if ( 200 !== $status ) {
        $message = is_array( $data ) && ! empty( $data['message'] ) ? $data['message'] : 'Titelbank API request failed.';
        return new WP_Error( 'titelbank_api_error', $message, [ 'status' => $status, 'body' => $data ] );
    }

    return $data; // e.g. ['isbn' => '...', 'data_json' => [...]]
}
```php

##Usage example:


```php
$result = mysite_get_titelbank_isbn( '9789026337086' );

if ( is_wp_error( $result ) ) {
    error_log( 'Titelbank API error: ' . $result->get_error_message() );
} else {
    // Example:
    // $result['data_json']['title']
    // $result['data_json']['author']
}
```php
