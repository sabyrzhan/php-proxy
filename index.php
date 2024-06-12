<?php
// Define the target URL
$targetUrl = "https://target-url";
$proxyUrl = "https://proxy-url";

// Get the client request URL
$clientUrl = $_SERVER['REQUEST_URI'];

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $targetUrl . $clientUrl);

$headers = [];
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// this function is called by curl for each header received
curl_setopt($ch, CURLOPT_HEADERFUNCTION,
    function($curl, $header) use (&$headers)
    {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) // ignore invalid headers
            return $len;

        $headers[strtolower(trim($header[0]))][] = trim($header[1]);

        return $len;
    }
);

$data = curl_exec($ch);
curl_close($ch);

$allowedHeaders = ['content-type'];
foreach ($headers as $headerName => $headerValue) {
    foreach($headerValue as $value) {
        if (in_array(strtolower($headerName), $allowedHeaders)) {
            header("$headerName: $value");
        }
    }
}

// replace links of target
$data = str_replace($targetUrl, $proxyUrl, $data);
echo $data;