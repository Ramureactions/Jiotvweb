<?php
$key = "JITENDRAUNATTI";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $url = "https://deencooper.space/JIO/live.php?id={$id}&key={$key}&e.m3u8";

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in the response

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        http_response_code(500);
        echo "Error: " . curl_error($ch);
    } else {
        // Separate headers and body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        // Forward headers to the client
        foreach (explode("\r\n", $headers) as $header) {
            if (stripos($header, 'Content-Type:') === 0 || stripos($header, 'Content-Disposition:') === 0) {
                header($header); // Forward Content-Type and Content-Disposition
            }
        }

        // Rewrite relative URLs in the body to absolute URLs
        $base_url = dirname($url) . '/';
        $body = preg_replace_callback('/(stream\.php\?[^#\s]+)/', function ($matches) use ($base_url) {
            return $base_url . $matches[1];
        }, $body);

        // Output the modified body
        echo $body;
    }

    curl_close($ch);
} else {
    http_response_code(400);
    echo "Error: Missing 'id' parameter.";
}