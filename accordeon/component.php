<? 
$Helper = new Helper;
if(!empty($arParams['FILTER'])){
    $arFilter = $arParams['FILTER'];
} else {
    $arFilter = [];
}
$arOrder = ['SORT' => 'DESC'];
$arSelect = ['ID', 'NAME', 'IBLOCK_ID'];
$dbSection  = CIBlockSection::GetList($arOrder, $arFilter, $arSelect);

while($arSection = $dbSection->GetNext()){
    $arOrder = ['SORT' => 'DESC'];

    if(!empty($arParams['FILTER_ELEMENTS'])){
        $arFilterFirst = [
            'IBLOCK_ID' =>  $arSection['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            'SECTION_ID' =>  $arSection['ID'],
        ];
        $arFilter = array_merge($arFilterFirst, $arParams['FILTER_ELEMENTS']);
    } else {
        $arFilter = [
            'IBLOCK_ID' =>  $arSection['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            'SECTION_ID' =>  $arSection['ID']
        ];
    }
  
    $select = ['ID', 'NAME', 'IBLOCK_ID', 'PROPERTY_PRICE', 'PROPERTY_OLD_PRICE'];
    $dbItems = CIBlockElement::GetList($arOrder, $arFilter, $select);
    while($arItem =$dbItems->Fetch()){
        $arSection['SUMS'][] = preg_replace("/[^0-9]/", '', $arItem['PROPERTY_PRICE_VALUE']);
        
        $arSection['SUBITEMS'][] = 
        [
            'NAME' => $arItem['NAME'],
            'PRICE' => $arItem['PROPERTY_PRICE_VALUE'],
            'OLD_PRICE' => $arItem['PROPERTY_OLD_PRICE_VALUE'],
            'ID' => $arItem['ID']
        ];
    };
    $arSection['MIN_PRICE'] = min($arSection['SUMS']);
    $arResult['ITEMS'][] = $arSection;
}

// $Helper->debuger($arResult['ITEMS']);
foreach($arResult['ITEMS'] as $key => $value){
    if(empty($value['SUBITEMS']))
        unset($arResult['ITEMS'][$key]);
}

// $Helper->debuger($arResult['ITEMS']);
$this->IncludeComponentTemplate();  