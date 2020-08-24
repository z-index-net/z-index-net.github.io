<?php

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: PHP',
        ],
    ],
]);

$content = file_get_contents('https://api.github.com/orgs/z-index-net/repos?per_page=100', false, $context);

$json = json_decode($content, false);

$xml = new SimpleXMLElement('<extensionset name="z-index development"/>');

foreach ($json as $repo) {

    if (false !== strpos($repo->name, 'joomla-')) {
        echo $repo->name . PHP_EOL;
        $detailsUrl = sprintf('https://raw.githubusercontent.com/z-index-net/%s/master/updatestream.xml', $repo->name);
        $updateStream = file_get_contents($detailsUrl, false, $context);
        $updateStreamXml = simplexml_load_string($updateStream);


        $client = $updateStreamXml->xpath('/updates/client');
        $folder = $updateStreamXml->xpath('/updates/folder');

        $targetPlatform = $updateStreamXml->xpath('/updates/update/targetplatform')[0]->attributes()['version'];

        $extension = $xml->addChild('extension');
        $extension->addAttribute('name', (string)$updateStreamXml->xpath('/updates/update/name')[0]);
        $extension->addAttribute('targetplatformversion', (string)$targetPlatform);
        $extension->addAttribute('version', (string)$updateStreamXml->xpath('/updates/update/version')[0]);
        $extension->addAttribute('type', (string)$updateStreamXml->xpath('/updates/update/type')[0]);

        if (!empty($client)) {
            $extension->addAttribute('client', (string)$client[0]);
        }

        if (!empty($folder)) {
            $extension->addAttribute('folder', (string)$folder[0]);
        }

        $extension->addAttribute('element', (string)$updateStreamXml->xpath('/updates/update/element')[0]);
        $extension->addAttribute('infourl', $repo->html_url);
        $extension->addAttribute('detailsurl', $detailsUrl);
    }
}

$dom = dom_import_simplexml($xml)->ownerDocument;
$dom->formatOutput = true;

file_put_contents('en/extensions.xml', $dom->saveXML());



