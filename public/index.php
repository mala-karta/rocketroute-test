<?php
/**
 * User: irene
 * Date: 08.08.2018
 */
define ('BASE_PATH', __DIR__ . '/..');
define ('WWW_PATH', __DIR__ );


require WWW_PATH . '/system.php';
require BASE_PATH . '/config.php';
require BASE_PATH . '/vendor/autoload.php';


$loader = new Twig_Loader_Filesystem(BASE_PATH . '/templates');
$twig = new Twig_Environment($loader);

echo $twig->render('index.html', array('googleApiKey' => $Config->getGoogleApiKey()));

die();
$client = new \GuzzleHttp\Client();

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><AUTH></AUTH>');

function array_to_xml( $data, SimpleXMLElement $xml ) {
    foreach( $data as $key => $value ) {
        if( is_numeric($key) ){
            $key = 'item'.$key; //dealing with <0/>..<n/> issues
        }
        if( is_array($value) ) {
            $subnode = $xml->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml->addChild("$key",htmlspecialchars("$value"));
        }
    }
}
$params = [
//    'AUTH' => [
        'USR'       => $Config->getRocketLogin(),
        'PASSWD'    => $Config->getRocketPassword(),
        //todo: set device id
        'DEVICEID'  => 'e138231a68ad82f054e3d756c6634ba1',
        'PCATEGORY' => 'RocketRoute',
        'APPMD5'    => $Config->getRocketAppMd5(),

//    ]
];
array_to_xml($params, $xml);
//vdie($xml->asXML());
//vdie($xml->asXML(), $params);

$xmlT = '<?xml version="1.0" encoding="UTF-8"?>
<AUTH>
  <USR>iryna.popovych252@gmail.com</USR>
  <PASSWD>fd6f97752adf2261d647537d328d5f00</PASSWD>
  <DEVICEID>e138231a68ad82f054e3d756c6634ba1</DEVICEID>
  <PCATEGORY>RocketRoute</PCATEGORY>
  <APPMD5>gzfVGWqgFszAC5qULG4v</APPMD5>
  <REQ></REQ>
</AUTH>';

$options = [
    'form_params' => ['req' => $xml->asXML()],
];
$response = $client->request('POST', $Config->getRocketAccessKeyUrl(), $options );
/*
$textResponse = '
<!--?xml version="1.0" encoding="UTF-8"?-->
<auth>
					  <result>SUCCESS</result>
					  <key>8608f7270603d5283448389c0c5f0c29</key>
					  <issubscript>1</issubscript>
					  <firstname>Iryna </firstname>
					  <lastname>Popovych</lastname>
					  <email>iryna.popovych252@gmail.com</email><currant_package_title>Free </currant_package_title><enddate>28 Days</enddate><expdate>2018-09-06 00:00:00</expdate>
					  </auth>
					  ';*/

//$response = $client->post($Config->getRocketAccessKeyUrl(), [
//    'body' => $xml->asXML(),
//    'headers' => [
//        'Content-Type' => 'application/x-www-form-urlencoded',
//    ]
//]);

$xmlTextResponse = $response->getBody()->getContents();

//check if success
$simpleXmlResponse = new SimpleXMLElement($xmlTextResponse);
vdie($simpleXmlResponse);

vdie($response->getBody()->getContents(),$response->getHeaderLine('content-type'), $response);

/*$params = [
    'location' => $Config->getRocketAccessKeyUrl(),
    'uri'      => $Config->getRocketAccessKeyUrl(),
    'AUTH' => [
        'USR'       => $Config->getRocketLogin(),
        'PASSWD'    => $Config->getRocketPassword(),
        //todo: set device id
        'DEVICEID'  => 'e138231a68ad82f054e3d756c6634ba1',
        'PCATEGORY' => 'RocketRoute',
        'APPMD5'    => $Config->getRocketAppMd5(),

    ]

];

$soapClient = new SoapClient(
    null,
    $params
);


vdie($soapClient, $soapClient->__getFunctions());*/