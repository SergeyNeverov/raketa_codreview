<?
use Bitrix\Main\Diag\Debug;

class Delivery
{
    /*api key убрал*/ 
    const NORDMAN = [
        'inn' => '',
        'phone' => ''
    ];

    const CDEK = [
        '', //номер
        '' //ключ
    ];

    const PR = [
        '',
        '',
    ];

    const DPD = [
        '',
        ''
    ];

    const BOXBERRY = '';

    const dimensionCDEK = [
        'length' => 450, //мм
        'width' => 320,
        'height' => 150
    ];

    const dimension = [
        'length' => 530, //мм
        'width' => 390,
        'height' => 150
    ];

    const city = [
        'PR' => 180000,
        'CDEK' => 393,
        'DPD' => 49694102
    ];

    const days = [
        1 => 'Пн',
        2 => 'Вт',
        3 => 'Ср',
        4 => 'Чт',
        5 => 'Пт',
        6 => 'Сб',
        7 => 'Вс',
    ];

    public static function priceCDEK($arParams, $item = false)
    {
        if ($arParams['weight'] < 500) {
            $arParams['weight'] = 500;
        }

        if ($item == true) {
            $profile = [
                ['id' => 136]
            ];
        } else {
            $profile = [
                ['id' => 136],
                ['id' => 137]
            ];

            if ($arParams['city_cdek'] == 393) {
                $profile = [
                    ['id' => 136],
                    ['id' => 137]
                ];
            }
        }

        $data = [
            'authLogin' => self::CDEK[0],
            'secure' => self::CDEK[1],
            'version' => '1.0',
            'dateExecute' => date('Y-m-d', strtotime('+1 day')),
            'senderCityId' => self::city['CDEK'],
            'receiverCityId' => $arParams['city_cdek'],
            'tariffList' => $profile,
            'goods' => [
                [
                    'weight' =>  $arParams['weight'] / 1000,
                    'length' => self::dimensionCDEK['length'] / 10,
                    'width' => self::dimensionCDEK['width'] / 10,
                    'height' => self::dimensionCDEK['height'] / 10
                ]
            ]
        ];

    //    Debug::writeToFile($arParams, '$data priceCDEK');

        $link = 'http://api.cdek.ru/calculator/calculate_tarifflist.php';
        $headers = ['Accept: application/json'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($curl);
        curl_close($curl);
        $arResult = json_decode($return, true);

//        Debug::writeToFile($arResult, '$arResult priceCDEK');

        return $arResult;
    }

    public static function priceDPD($arParams, $item = false)
    {
        $data = [
            'auth' => [
                'clientNumber' => self::DPD[0],
                'clientKey' => self::DPD[1]
            ],
            'pickup' => [
                'cityId' => self::city['DPD']
            ],
            'delivery' => [
                'cityName' => $arParams['city_name'],
                'index' => $arParams['city_index'],
            ],
            'selfPickup' => true,
            'selfDelivery' => false, 
            'pickupDate' => date('Y-m-d', strtotime('+1 day')),
            'declaredValue' => 0,
            'serviceCode' => 'PCL',
            'parcel' => [
                [
                    'weight' => 1.012,
                    'length' => self::dimension['length'] / 10,
                    'width' => self::dimension['width'] / 10,
                    'height' => self::dimension['height'] / 10,
                    'quantity' => 1
                ]
            ],
        ];
        $arResult = [];
        try {
            ini_set('default_socket_timeout', 5);
            $client = new SoapClient('http://ws.dpd.ru/services/calculator2?wsdl');

            if ($item == true) {
                $selfDelivery = [false];
            } else {
                $selfDelivery = [true, false];
            }

            foreach ($selfDelivery as $type) {
                $data['selfDelivery'] = $type;
                $arRequest['request'] = $data;

                $return = $client->getServiceCostByParcels2($arRequest);
                $arReturn = json_decode(json_encode($return), true);

                if ($type == true) {
                    $arResult['post'] = $arReturn;
                } elseif ($type == false) {
                    $arResult['courier'] = $arReturn;
                }

            }
        } catch (Throwable $e) {
            return false;
        }

        return $arResult;
    }

    public static function pricePR($arParams)
    {
        $object = 23020;
        $url = 'https://delivery.pochta.ru/delivery/v1/calculate?json&';
        $url2 = 'https://tariff.pochta.ru/tariff/v1/calculate?json&';

        $data = http_build_query([
            'object' => $object,
            'to' => $arParams['city_index'],
            'from' => self::city['PR'],
            'sumin' => !empty($arParams['total']) ? $arParams['total'] : 0,
            'weight' => !empty($arParams['weight']) ? $arParams['weight'] : 0,
            'sumoc' => !empty($arParams['total']) ? $arParams['total'] : 0
        ]); 
        //сначала сроки 
        $url .= $data;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($curl);
        curl_close($curl);

        // затем стоимость 
        $curl = curl_init(); 
        $url2 .= $data;
        curl_setopt($curl, CURLOPT_URL, $url2);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return2 = curl_exec($curl);
        curl_close($curl);

        $arResponse = array_merge(json_decode($return, true), json_decode($return2, true)); 
        $arResult = [
            'total-rate' => $arResponse['paynds'],
            'delivery-time' => [
                // 'min-days' => $arResponse['delivery']['min'],
                'max-days' => $arResponse['delivery']['max']
            ]
        ];
        return $arResult;
    }

    public static function priceEMS($arParams)
    {
        $data = [
            'index-from' => self::city['PR'],
            'index-to' => $arParams['city_index'],
            'dimension' => [
                'length' => self::dimension['length'] / 10,
                'width' => self::dimension['width'] / 10,
                'height' => self::dimension['height'] / 10,
            ],
            'mass' => !empty($arParams['weight']) ? $arParams['weight'] : 0,
            'declared-value' => !empty($arParams['total']) ? $arParams['total'] : 0,
            'mail-category' => 'WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY',
            'mail-type' => 'EMS'
        ];

        $link ='https://otpravka-api.pochta.ru/1.0/tariff';
        $headers = [
            'Authorization: AccessToken ' . self::PR[0],
            'X-User-Authorization: Basic ' . self::PR[1],
            'Content-Type: application/json;charset=UTF-8'
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($curl);
        curl_close($curl);
        $arResult = json_decode($return, true);

        return $arResult;
    }

    public static function postPR($City_INDEX)
    {
        $arPost = [];
        $data = [
            'filter' => 'ALL',
            'top' => count($City_INDEX) + 5,
        ];

        $link = 'https://otpravka-api.pochta.ru/postoffice/1.0/nearby';
        $headers = [
            'Authorization: AccessToken ' . self::PR[0],
            'X-User-Authorization: Basic ' . self::PR[1],
            'Content-Type: application/json;charset=UTF-8'
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $link . '?' . http_build_query($data));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($curl);
        curl_close($curl);
        $arReturn = json_decode($return, true);

        foreach ($arReturn as $item) {
            $workTime = [];
            foreach ($item['working-hours'] as $time) {
                if (!empty($time['begin-worktime'])) {
                    $time['begin-worktime'] = explode(':', $time['begin-worktime']);
                    $time['end-worktime'] = explode(':', $time['end-worktime']);
                    $workTime[self::days[$time['weekday-id']]] = $time['begin-worktime'][0] . ':' . $time['begin-worktime'][1] . '...' . $time['end-worktime'][0] . ':' . $time['end-worktime'][1];
                }
            }

            $arPost[] = [
                'id' => $item['postal-code'],
                'postalCode' => $item['postal-code'],
                'city' => $item['settlement'],
                'address' => $item['address-source'],
                'workTime' => $workTime,
                'coor' => [$item['latitude'], $item['longitude']]
            ];
        }

        return $arPost;
    }

    public static function postCDEK($City_CDEK)
    {
        $arPost = [];
        $data = [
            'cityid' => $City_CDEK
        ];

        $link = 'https://integration.cdek.ru/pvzlist/v1/json';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $link . '?' . http_build_query($data));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($curl);
        curl_close($curl);
        $arReturn = json_decode($return, true);
        $arReturn = $arReturn['pvz'];

        foreach ($arReturn as $item) {
            $workTime = [];
            foreach ($item['workTimeYList'] as $time) {
                if (!empty($time['periods'])) {
                    $time['periods'] = explode('/', $time['periods']);
                    $workTime[self::days[$time['day']]] = $time['periods'][0] . '...' . $time['periods'][1];
                }
            }

            $arPost[] = [
                'id' => $item['code'],
                'postalCode' => $item['postalCode'],
                'city' => $item['city'],
                'metroStation' => $item['metroStation'],
                'address' => $item['address'],
                'addressComment' => $item['addressComment'],
                'workTime' => $workTime,
                'phone' => $item['phone'],
                'coor' => [$item['coordY'], $item['coordX']],
                'pay' => $item['haveCashless'] == 1 ? 'Картой или наличными' : 'Наличными'
            ];
        }
        // Debug::writeToFile($arReturn, '$data dotsСdek');   
        return $arPost;
    }

    public static function postDPD($City_Name)
    {
        $arPost = [];
        $data = [
            'auth' => [
                'clientNumber' => self::DPD[0],
                'clientKey' => self::DPD[1]
            ],
            'cityName' => $City_Name
        ];

        ini_set('default_socket_timeout', 5);
        $client = new SoapClient('http://ws.dpd.ru/services/geography2?wsdl', array('connection_timeout' => 5));
        try {
            $arRequest['request'] = $data;
            $return = $client->getParcelShops($arRequest);
            $arReturn = json_decode(json_encode($return), true);
            $arReturn = $arReturn['return']['parcelShop'];
            if (empty($arReturn[0])) {
                $array0[0] = $arReturn;
                $arReturn = $array0;
            }

            foreach ($arReturn as $item) {
                if ($item['parcelShopType'] == 'П') {
                    continue;
                }

                if (empty($item['schedule'][0])) {
                    $array0[0] = $item['schedule'];
                    $item['schedule'] = $array0;
                }
                foreach ($item['schedule'] as $schedule) {
                    if ($schedule['operation'] == 'PaymentByBankCard') {
                        $pay = 1;
                    }
                }

                $workTime = [];
                if (empty($item['schedule'][0]['timetable'][0])) {
                    $array0[0] = $item['schedule'][0]['timetable'];
                    $item['schedule'][0]['timetable'] = $array0;
                }
                foreach ($item['schedule'][0]['timetable'] as $time) {
                    $time['weekDays'] = explode(',', $time['weekDays']);
                    $time['workTime'] = explode('-', $time['workTime']);

                    foreach ($time['weekDays'] as $value) {
                        if (!empty($time['workTime'][1])) {
                            $workTime[$value] = trim($time['workTime'][0]) . '...' . trim($time['workTime'][1]);
                        } else {
                            $workTime[$value] = trim($time['workTime'][0]);
                        }
                    }
                }

                $arPost[] = [
                    'id' => $item['code'],
                    'postalCode' => $item['address']['index'],
                    'city' => $item['address']['cityName'],
                    'address' => $item['address']['streetAbbr'] . '. ' . $item['address']['street'] . ', ' . $item['address']['houseNo'],
                    'addressComment' => $item['address']['descript'],
                    'workTime' => $workTime,
                    'coor' => [$item['geoCoordinates']['latitude'], $item['geoCoordinates']['longitude']],
                    'pay' => $pay == 1 ? 'Картой или наличными' : 'Наличными'
                ];
            }
        } catch (Throwable $e) {
        }

        return $arPost;
    }

    public static function orderCDEK($arOrder, $arProducts)
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'utf-8');

        $xml->startElement('deliveryrequest');
        $xml->writeAttribute('number', $arOrder['id']);
        $xml->writeAttribute('date', $arOrder['date_created']);
        $xml->writeAttribute('account', self::CDEK[0]);
        $xml->writeAttribute('secure', self::CDEK[1]);
        $xml->writeAttribute('ordercount', '1');

        $xml->startElement('order');
        $xml->writeAttribute('number', $arOrder['id']);
        $xml->writeAttribute('phone', $arOrder['phone']);
        $xml->writeAttribute('recipientemail', $arOrder['email']);
        $xml->writeAttribute('recipientname', $arOrder['fio']);
        $xml->writeAttribute('sendcitycode', self::city['CDEK']);
        $xml->writeAttribute('reccitycode', $arOrder['city_id']);
        $xml->writeAttribute('tarifftypecode', $arOrder['tariff']);

        $xml->startElement('address');
        if ($arOrder['tariff'] == 136) {
            $xml->writeAttribute('PvzCode', $arOrder['post']);
        } else {
            $address = explode(',', $arOrder['address']);
            if (strlen(trim($address[2])) > 5 && empty($address[3])) {
                $address[2] = '';
            }
            $xml->writeAttribute('street', trim($address[0]));
            $xml->writeAttribute('house', trim($address[1]));
            if (!empty($address[2])) {
                $xml->writeAttribute('flat', trim($address[2]));
            }
        }
        $xml->endElement();

        if ($arOrder['pay'] == 4) {
            if ($arOrder['delivery_price'] > 0) {
                $xml->startElement('DeliveryRecipientCostAdv');
                $xml->writeAttribute('Threshold', (int)ceil($arOrder['price']));
                $xml->writeAttribute('VATRate', 'VAT20');
                $xml->writeAttribute('VATSum', number_format($arOrder['delivery_price'] / 120 * 20, 2, '.', ''));
                $xml->writeAttribute('Sum', $arOrder['delivery_price']);
                $xml->endElement();
            }
        }

        $xml->startElement('seller');
        $xml->writeAttribute('name', 'NORDMAN');
        $xml->endElement();

        $xml->startElement('package');
        $xml->writeAttribute('number', $arOrder['id']);
        $xml->writeAttribute('barcode', $arOrder['id'] . '-barcode');
        $xml->writeAttribute('sizea', self::dimensionCDEK['length'] / 10);
        $xml->writeAttribute('sizeb', self::dimensionCDEK['width'] / 10);
        $xml->writeAttribute('sizec', self::dimensionCDEK['height'] / 10);
        $xml->writeAttribute('weight', $arOrder['weight']);
        foreach ($arProducts as $product) {
            $xml->startElement('item');
            $xml->writeAttribute('amount', (int)$product['quantity']);
            $xml->writeAttribute('warekey', $product['code']);
            $xml->writeAttribute('cost', $product['price']);
            if ($arOrder['pay'] != 4) {
                $xml->writeAttribute('payment', 0);
            } else {
                $xml->writeAttribute('payment', $product['price']);
                if (!empty($product['tag1162'])) {
                    $xml->writeAttribute('Marking', $product['tag1162']);
                }
            }
            $xml->writeAttribute('comment', $product['name']);
            $xml->writeAttribute('PaymentVATRate', 'VAT' . $product['vat'] * 100);
            $xml->writeAttribute('PaymentVATSum', number_format(($product['price'] / ($product['vat'] * 100 + 100)) * $product['vat'] * 100, 2, '.', ''));
            $xml->writeAttribute('weight', (int)$product['weight']);
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        $orderDelivery = ['xml_request' => $xml->outputMemory()];

        $link = 'https://integration.cdek.ru/new_orders.php';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $orderDelivery);
        $return = curl_exec($curl);
        curl_close($curl);
        $arReturn = simplexml_load_string($return);
        $arReturn = json_decode(json_encode($arReturn), true);

        return $arReturn;

        /* Удаление CDEK
        $xml = new XMLWriter();
        $xml->openMemory();

        $xml->startDocument('1.0', 'utf-8');
        //deliveryrequest
        $xml->startElement('deleterequest');
        $xml->writeAttribute('number', '1136743325');
        $xml->writeAttribute('date', '2019-09-06');
        $xml->writeAttribute('account', '5caa3c7899094f4ab187dfc5c28b2742');
        $xml->writeAttribute('secure', 'e1143e6ffdbc8ae65dc916542b9d6517');
        $xml->writeAttribute('ordercount', '1');

        $xml->startElement('order');
        $xml->writeAttribute('number', '10000000');
        $xml->endElement();

        $xml->endElement();
        $xml->endDocument();

        $orderDelivery = [];
        $orderDelivery = ['xml_request' => $xml->outputMemory()];

        $link = "https://integration.cdek.ru/delete_orders.php";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $orderDelivery);
        $return = curl_exec($curl);
        curl_close($curl);
        $data = simplexml_load_string($return);
        $data = json_decode(json_encode($data), true);

        echo "<pre>";
        print_r($data);
        echo "</pre>";*/
    }

    public static function orderDPD($arOrder, $arProducts)
    {
        $data['auth'] = [
            'clientNumber' => self::DPD[0],
            'clientKey' => self::DPD[1]
        ];

        $data['header'] = [
            'datePickup' => date("Y-m-d", strtotime(date("Y-m-d"))),
            'pickupTimePeriod' => '9-13'
        ];

        $data['header']['senderAddress'] = [
            'name' => 'ООО "Псков-Полимер"',
            'countryName' => 'Россия',
            'city' => 'Псков',
            'street' => 'Железнодорожная',
            'house' => '60',
            'index' => '180004',
            'contactFio' => 'Сергей',
            'contactPhone' => self::NORDMAN['phone'],
        ];

        $data['order'] = [
            'orderNumberInternal' => $arOrder['id'],
            'serviceCode' => 'PCL',
            'serviceVariant' => $arOrder['service'],
            'cargoNumPack' => 1,
            'cargoRegistered' => false,
            'cargoWeight' => $arOrder['weight'] / 1000,
        ];

        if ($arOrder['service'] == 'ДД') {
            $address = explode(',', $arOrder['address']);
            $house = explode('/', $address[1]);
            if (strlen(trim($address[2])) > 5 && empty($address[3]) && !empty($address[2])) {
                $address[3] = $address[2];
                $address[2] = '';
            }

            $data['order']['receiverAddress'] = [
                'name' => $arOrder['fio'],
                'countryName' => $arOrder['country'],
                'region' => $arOrder['region'],
                'city' => $arOrder['city'],
                'street' => trim($address[0]),
                'house' => trim($house[0]),
                'contactFio' => $arOrder['fio'],
                'contactPhone' => $arOrder['phone'],
            ];

            if (!empty($house[1])) {
                $data['order']['receiverAddress']['houseKorpus'] = trim($house[1]);
            }
            if (!empty($address[2])) {
                $data['order']['receiverAddress']['flat'] = trim($address[2]);
            }
            if (!empty($address[3])) {
                $data['order']['receiverAddress']['index'] = trim($address[3]);
            }
        } else {
            $data['order']['receiverAddress'] = [
                'name' => $arOrder['fio'],
                'countryName' => $arOrder['country'],
                'region' => $arOrder['region'],
                'city' => $arOrder['city'],
                'terminalCode' => $arOrder['post'],
                'contactFio' => $arOrder['fio'],
                'contactPhone' => $arOrder['phone'],
            ];
        }

        if ($arOrder['pay'] == 4) {

            foreach ($arProducts as $product) {
                if (!empty($product['gtin'])) {
                    $data['order']['is_unique_marking'] = true;
                    break;
                }
            }

            foreach ($arProducts as $i => $product) {
                $data['order']['unitLoad'][$i] = [
                    'descript' => $product['name'],
                    'article' => $product['code'],
                    'count' => (int)$product['quantity'],
                    'declared_value' => number_format($product['price'], 2, '.', ''),
                    'npp_amount' => number_format($product['price'], 2, '.', ''),
                    'vat_percent' => $product['vat'] * 100
                ];

                if (!empty($product['gtin'])) {
                    $data['order']['unitLoad'][$i]['GTIN'] = $product['gtin'];
                }
                if (!empty($product['serial'])) {
                    $data['order']['unitLoad'][$i]['serial'] = $product['serial'];
                }
            }

            $data['order']['unitLoad'][] = [
                'descript' => 'Доставка',
                'article' => 'delivery',
                'count' => 1,
                'declared_value' => number_format($arOrder['delivery_price'], 2, '.', ''),
                'npp_amount' => number_format($arOrder['delivery_price'], 2, '.', ''),
                'vat_percent' => 20,
            ];
        } else {
            $data['order']['cargoValue'] = number_format($arOrder['price'], 2, '.', '');
        }

        $i = 0;
        foreach ($arProducts as $item) {
            if ($i == 0) {
                $data['order']['cargoCategory'] .= $item['name'];
            } else {
                $data['order']['cargoCategory'] .= ', ' . $item['name'];
            }
            $i++;
        }

        $client = new SoapClient("http://ws.dpd.ru/services/order2?wsdl");

        try {
            $data['orders'] = $data;
            $return = $client->createOrder($data);
            $arReturn = json_decode(json_encode($return), true);
        } catch (Throwable $e) {
            $arReturn = $e;
        }

        /*$data['auth'] = [
            'clientNumber' => self::DPD[0],
            'clientKey' => self::DPD[1]
        ];

        $data['order'] = [
            'orderNumberInternal' => $arOrder['id'],
        ];

        $client = new SoapClient("http://ws.dpd.ru/services/order2?wsdl");

        try {
            $data['orderStatus'] = $data;
            $return = $client->getOrderStatus($data);
            $arReturn = json_decode(json_encode($return), true);
        } catch (Throwable $e) {
            $arReturn = $e;
        }*/

        return $arReturn;
    }

    public static function orderPR($arOrder, $arProducts)
    {
        $address = explode(',', $arOrder['address']);
        $arOrder['phone'] = preg_replace('/[^0-9]/', '', $arOrder['phone']);

        if (strlen(trim($address[2])) > 5 && empty($address[3])) {
            $address[3] = $address[2];
            $address[2] = '';
        }

        $data = [
            'address-type-to' => 'DEFAULT',
            'recipient-name' => $arOrder['fio'],
            'tel-address' => $arOrder['phone'],

            'mail-direct' => 643,
            'region-to' => $arOrder['region'],
            'place-to' => $arOrder['city'],
            'street-to' => trim($address[0]),
            'house-to' => trim($address[1]),
            'index-to' => trim($address[3]),

            'postoffice-code' => $arOrder['post-office'],
            'order-num' => $arOrder['id'],
            'mail-type' => $arOrder['type'],
            'courier' => !empty($arOrder['courier']) ? $arOrder['courier'] : false,
            'insr-value' => $arOrder['price'] * 100,
            'mass' => $arOrder['order_weight'] ? $arOrder['order_weight'] : $arOrder['weight'],
            'fragile' => false
        ];

        if ($arOrder['pay'] == 4) {
            foreach ($arProducts as $i => $product) {
                $data['goods']['items'][$i] = [
                    'description' => $product['name'],
                    'item-number' => $product['code'],
                    'quantity' => (int)$product['quantity'],
                    'supplier-inn' => self::NORDMAN['inn'],
                    'supplier-phone' => self::NORDMAN['phone'],
                    'value' => (int)($product['price'] * 100),
                    'vat-rate' => (int)($product['vat'] * 100),
                    'weight' => (int)$product['weight'],
                ];

                if (!empty($product['tag1162'])) {
                    $data['goods']['items'][$i]['code'] = str_replace(' ', '', $product['tag1162']);
                }
            }

            $data['goods']['items'][] = [
                'description' => 'Доставка',
                'quantity' => 1,
                'supplier-inn' => self::NORDMAN['inn'],
                'supplier-phone' => self::NORDMAN['phone'],
                'value' => (int)($arOrder['delivery_price'] * 100),
                'vat-rate' => 20
            ];
        }

        if (!empty($address[2])) {
            $data['room-to'] = trim($address[2]);
        }

        if ($arOrder['pay'] == 4) {
            $data['delivery-with-cod'] = true;
            $data['mail-category'] = 'WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY';
            $data['payment'] = $arOrder['price'] * 100;
        } else {
            $data['mail-category'] = 'WITH_DECLARED_VALUE';
        }

        $link = 'https://otpravka-api.pochta.ru/1.0/user/backlog';
        $headers = [
            'Authorization: AccessToken ' . self::PR[0],
            'X-User-Authorization: Basic ' . self::PR[1],
            'Content-Type: application/json',
            'Accept: application/json;charset=UTF-8',
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([$data]));
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($curl);
        curl_close($curl);
        $arReturn = json_decode($return, true);

        $link = 'https://otpravka-api.pochta.ru/1.0/backlog/' . $arReturn['result-ids'][0];
        $headers = [
            'Authorization: AccessToken ' . self::PR[0],
            'X-User-Authorization: Basic ' . self::PR[1],
            'Content-Type: application/json',
            'Accept: application/json;charset=UTF-8',
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($curl);
        curl_close($curl);
        $arOrder = json_decode($return, true);
        $arOrder['id'] = $arReturn['result-ids'][0];

        return $arOrder;
    }

    public static function cityIdCDEK($arParams) {
        $data = [
            'authLogin' => self::CDEK[0],
            'secure' => self::CDEK[1],
            'version' => '1.0',
            'cityName' => $arParams['cityName'],
        ];

        if (!empty($arParams['postcode']))
            $data['postcode'] = $arParams['postcode'];

        if (!empty($arParams['country']))
            $data['countryCode'] = $arParams['country'];

        $params = http_build_query($data);

        $link = 'http://integration.cdek.ru/v1/location/cities/json?' . $params;
        $headers = ['Accept: application/json'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($curl);
        curl_close($curl);
        $arResult = json_decode($return, true);
        
        return $arResult[0]['cityCode'];
    }


    /* 
        ДОСТАВКА BOXBERRY
    
       для того чтобы рассчитать стоимость доставки у boxberry 
       в запросе необходимо передать Code - внутренний код ПВЗ
       в текущих 2 методах получаем Code 
       для расчета по городу 
       1. получаем список городов 
       2. получаем внутренний CityCode - код города
       3. С CityCode получаем Code первого элемента в boxberryPointId()
    */ 

    private static function boxberryCityId($arParams){
        $link =  'https://api.boxberry.ru/json.php?';
        $data = http_build_query([
            'token' => self::BOXBERRY,
            'method' => 'ListCities'
        ]);
        $link .= $data;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $return = curl_exec($curl);
        curl_close($curl);
        $arReturn = json_decode($return, true);

        foreach($arReturn as $item){
            if(strtolower($item['Name']) == strtolower($arParams)){
                $cityCode = $item['Code'];
                break;
            }
        }
        return $cityCode;
    }

    public static function boxberryPointId($arParams)
    {
        $cityCode = self::boxberryCityId($arParams);
        if(!empty($cityCode)){
            $link = 'https://api.boxberry.ru/json.php?';
            $data = http_build_query([
                'token' => self::BOXBERRY,
                'method' => 'ListPoints',
                'CityCode' => $cityCode
            ]);
            // Debug::writeToFile($data, '$data idBoxbery');      
            $link .= $data;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            $return = curl_exec($curl);
            curl_close($curl);
            $arReturn = json_decode($return, true);    
            $pointId = $arReturn[0]['Code'];
            // Debug::writeToFile($pointId, '$data idCityBoxbery');   
            if(!empty($pointId))
                return $pointId; 
            else
                return null;    
        } else {
            return null;
        } 
    }

    /*
        расчет стоимости доставки boxberry
        $arParams['target'] - код точки получаемый с метода boxberryPointId
    */ 

    public static function priceBoxbery($arParams)
    {
        if ($arParams['weight'] < 500) {
            $arParams['weight'] = 500;
        } 
        
        // Debug::writeToFile($arParams, '$arParams priceBoxbery');
        $link = 'https://api.boxberry.ru/json.php?';

        $target = $arParams['target'];

        if($target) {
            $data = http_build_query([
                'weight' => $arParams['weight'],
                'token' => self::BOXBERRY,
                'target' => $target,
                'method' => 'DeliveryCosts',
                'ordersum' => 'total',
                'zip' => $arParams['city_index']
            ]);
            $link .= $data;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            $return = curl_exec($curl);
            curl_close($curl);
            $arResult = json_decode($return, true);  
            // Debug::writeToFile($arResult, '$data priceBoxbery');   
            return $arResult;   
        } else {
            $data = http_build_query([
                'weight' => $arParams['weight'],
                'token' => self::BOXBERRY,
                'method' => 'DeliveryCosts',
                'ordersum' => 'total',
                'zip' => $arParams['city_index']
            ]);
            $link .= $data;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            $return = curl_exec($curl);
            curl_close($curl);
            $arResult = json_decode($return, true);  
            // Debug::writeToFile($arResult, '$data priceBoxbery');   
            return $arResult; 
        }
    }

    /*
        список точек ПВЗ
    */

    public static function postBoxberry($arParams)
    {
        Debug::writeToFile($arParams, '$data arParams');
        $cityCode = self::boxberryCityId($arParams['city_name']);
        if(!empty($cityCode)){
            $link = 'https://api.boxberry.ru/json.php?';
            $data = http_build_query([
                'token' => self::BOXBERRY,
                'method' => 'ListPoints',
                'CityCode' => $cityCode,
                'prepaid' => $arParams['delay']
            ]);
            $link .= $data;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            $return = curl_exec($curl);
            curl_close($curl);
            $arReturn = json_decode($return, true); 
            // Debug::writeToFile($arReturn, '$data postBoxbery'); 
        
            foreach($arReturn as $item){
                $workTime = [];
                foreach (explode(',', $item['WorkShedule']) as $time) {
                    $key =  explode(': ',  $time)[0];
                    $workTime[$key] = explode(': ',  $time)[1];
                }
                $arPost[] = [
                    'id' => $item['Code'],
                    'postalCode' => explode(',', $item['Address'])[0],
                    'city' => $item['Settlement'],
                    'address' => $item['AddressReduce'],
                    'coor' =>explode(',', $item['GPS']),
                    'addressComment' => $item['TripDescription'],
                    'workTime' => $workTime
                ];
            }
          
            return $arPost;
        }
    }

    /* создание заказа*/ 
    public static function orderBoxberry($arOrder, $arProducts)
    {
        
        // Debug::writeToFile($arOrder, 'BXB order');
        $curl = curl_init();
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
        $link = 'https://api.boxberry.ru/json.php';

        // Debug::writeToFile($arProducts, 'BXB product');
        foreach ($arProducts as $i => $product) {
            
            $items[] = [
                'name' => $product['name'],
                'quantity' => (int)$product['quantity'],
                'price' => number_format($product['price'], 2, '.', ''),
                'id' =>$product['code'],
                'marking_crpt' => $product['gtin']
            ];

        }

        $addressArr = explode(',', $arOrder['address']);
        $index = $addressArr[count($addressArr) -1];

        $data = [
            'token' => '',
            'method' => 'ParselCreate',
            'sdata' => [
                'order_id' => $arOrder['id'],
                'price' => $arOrder['price'],
                'delivery_sum' => $arOrder['delivery_price'],
                'vid' => $arOrder['vid'],
                "kurdost"=> [
                    'index'=> $index,
                    'citi'=> $arOrder['city'],
                    'addressp'=>$arOrder['address'],
                    'delivery_date'=> '',
                    'timesfrom1'=> '',
                    'timesto1'=> '',
                    'comentk'=> '',
                ],
                'shop' => [
                    'name' => $arOrder['post']
                ],
                'customer' => [
                        "fio"=> $arOrder['fio'],
                        "phone"=> $arOrder['phone'],
                        "phone2"=> "",
                        "email"=> $arOrder['email']
                ],
                "items" =>  $items,
                "notice"=> "",
                "weights"=> [
                    "weight" => $arOrder['weight'],
                    "barcode"=> "",
                    "x"=> self::dimension['length'] / 10,
                    "y"=> self::dimension['height'] / 10,
                    "z"=> self::dimension['width'] / 10,
                ],
            ],
        ];

        if($arOrder['pay'] == 4 && $arOrder['vid'] == 1){
            $data['sdata']['payment_sum'] = $arOrder['price'];
        }

        Debug::writeToFile($data, 'BXB data');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_URL, $link);
        $response = curl_exec($curl);
        curl_close($curl);
        $arReturn = json_decode($response, true);
        return $arReturn;
    }

}