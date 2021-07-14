<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
$Helper = New Helper;
$assets = $Helper::assetsPath();
?>

<? if(!empty($arResult['ITEMS'])){?>
    <section class="section reviews reviews_dark reviews_slider">
        <div class="container">
            <h2 class="title title_section title_light">Отзывы</h2>
            <div class="reviews__slider"> 
                <div class="slider" data-count="2" data-infinite="true" data-type="reviews"> 
                <div class="slider__buttons">
                        <a class="slider__arrow slider__arrow_left" href="javascritp:void(0)" data-slider-arrow="prev">
                            <svg>
                                <use xlink:href="<?=$assets?>/img/symbols.svg#svg-chevron-left"></use>
                            </svg>
                        </a>
                        <a class="slider__arrow slider__arrow_right" href="javascritp:void(0)" data-slider-arrow="next">
                            <svg>
                                <use xlink:href="<?=$assets?>img/symbols.svg#svg-chevron-right"></use>
                            </svg>
                        </a>
                    </div>
                <ul class="slider__list">
                    <? foreach($arResult['ITEMS'] as $item){
                            $doctor = [
                                'NAME' => $item['DISPLAY_PROPERTIES']['DOCTOR']['LINK_ELEMENT_VALUE'][$item['DISPLAY_PROPERTIES']['DOCTOR']['VALUE']]['NAME'],
                                'URL' =>  $item['DISPLAY_PROPERTIES']['DOCTOR']['LINK_ELEMENT_VALUE'][$item['DISPLAY_PROPERTIES']['DOCTOR']['VALUE']]['DETAIL_PAGE_URL']
                            ];
                            $service = [
                                'NAME' =>  $item['DISPLAY_PROPERTIES']['SERVICE_ITEM']['LINK_ELEMENT_VALUE'][$item['DISPLAY_PROPERTIES']['SERVICE_ITEM']['VALUE']]['NAME'],
                                'URL' => $item['DISPLAY_PROPERTIES']['SERVICE_ITEM']['LINK_ELEMENT_VALUE'][$item['DISPLAY_PROPERTIES']['SERVICE_ITEM']['VALUE']]['DETAIL_PAGE_URL']
                            ];
                        ?>
                        <li class="slider__item">
                        <div class="review review_dark">
                            <div class="review__top">
                                <p class="review__author"><?=$item['NAME'];?></p>
                                <span class="review__date"><?=explode(' ', $item['DATE_CREATE'])[0];?></span>
                                <p class="review__type">Врач: 
                                    <a class="review__type-link" target="_blank" href="<?=$doctor['URL'];?>"><?=$doctor['NAME'];?></a>
                                </p>
                                    <? if(strlen($service['NAME']) > 0){?>
                                        <p class="review__type">Услуга:
                                            <a class="review__type-link"  target="_blank" href="<?=$service['URL'];?>"><?=$service['NAME'];?></a>
                                        </p>
                                    <?}?>
                            
                            </div>
                            <div class="review__rating">
                                <svg xmlns="http://www.w3.org/2000/svg" width="106" height="18" viewBox="0 0 106 18" fill="none">
                                    <path d="M9 1.13337L11.1952 5.58141L11.3119 5.81788L11.5729 5.8558L16.4816 6.56908L12.9296 10.0314L12.7408 10.2155L12.7854 10.4754L13.6239 15.3642L9.23341 13.056L9 12.9333L8.76659 13.056L4.37611 15.3642L5.21462 10.4754L5.2592 10.2155L5.07036 10.0314L1.51839 6.56908L6.42709 5.8558L6.68806 5.81788L6.80476 5.58141L9 1.13337Z" 
                                    <? if($item['PROPERTIES']['RAITING']['VALUE'] >= 1) {?> fill="#F1C644" <?}else{?> fill="white" <?}?> stroke="#F1C644" stroke-width="1.00318"/>
                                    <path d="M31 1.13337L33.1952 5.58141L33.3119 5.81788L33.5729 5.8558L38.4816 6.56908L34.9296 10.0314L34.7408 10.2155L34.7854 10.4754L35.6239 15.3642L31.2334 13.056L31 12.9333L30.7666 13.056L26.3761 15.3642L27.2146 10.4754L27.2592 10.2155L27.0704 10.0314L23.5184 6.56908L28.4271 5.8558L28.6881 5.81788L28.8048 5.58141L31 1.13337Z" 
                                    <? if($item['PROPERTIES']['RAITING']['VALUE'] >= 2) {?> fill="#F1C644" <?}else{?> fill="white" <?}?>  stroke="#F1C644" stroke-width="1.00318"/>
                                    <path d="M53 1.13337L55.1952 5.58141L55.3119 5.81788L55.5729 5.8558L60.4816 6.56908L56.9296 10.0314L56.7408 10.2155L56.7854 10.4754L57.6239 15.3642L53.2334 13.056L53 12.9333L52.7666 13.056L48.3761 15.3642L49.2146 10.4754L49.2592 10.2155L49.0704 10.0314L45.5184 6.56908L50.4271 5.8558L50.6881 5.81788L50.8048 5.58141L53 1.13337Z" 
                                    <? if($item['PROPERTIES']['RAITING']['VALUE'] >= 3) {?> fill="#F1C644" <?}else{?> fill="white" <?}?> stroke="#F1C644" stroke-width="1.00318"/>
                                    <path d="M75 1.13337L77.1952 5.58141L77.3119 5.81788L77.5729 5.8558L82.4816 6.56908L78.9296 10.0314L78.7408 10.2155L78.7854 10.4754L79.6239 15.3642L75.2334 13.056L75 12.9333L74.7666 13.056L70.3761 15.3642L71.2146 10.4754L71.2592 10.2155L71.0704 10.0314L67.5184 6.56908L72.4271 5.8558L72.6881 5.81788L72.8048 5.58141L75 1.13337Z" 
                                    <? if($item['PROPERTIES']['RAITING']['VALUE'] >= 4) {?> fill="#F1C644" <?}else{?> fill="white" <?}?> stroke="#F1C644" stroke-width="1.00318"/>
                                    <path d="M97 1.13337L99.1952 5.58141L99.3119 5.81788L99.5729 5.8558L104.482 6.56908L100.93 10.0314L100.741 10.2155L100.785 10.4754L101.624 15.3642L97.2334 13.056L97 12.9333L96.7666 13.056L92.3761 15.3642L93.2146 10.4754L93.2592 10.2155L93.0704 10.0314L89.5184 6.56908L94.4271 5.8558L94.6881 5.81788L94.8048 5.58141L97 1.13337Z" 
                                    <? if($item['PROPERTIES']['RAITING']['VALUE'] >= 5) {?> fill="#F1C644" <?}else{?> fill="white" <?}?> stroke="#F1C644" stroke-width="1.00318"/>
                                </svg>
                            </div>
                            <div class="review__text review__text_dark">
                                <?=$item['PREVIEW_TEXT']?></div>
                                <a class="review__link" href="#" data-modal-open="review">Читать весь отзыв</a>
                                <? if(!empty($item['PROPERTIES']['LINK']['VALUE'])){?>
                                    <a class="review__link" target="_blank" href="<?=$item['PROPERTIES']['LINK']['VALUE']?>"> Смотреть оригинал отзыва</a>
                                <?}?>
                            <div class="review__hidden" data-modal-content="">
                                <?=$item['DETAIL_TEXT'];?>
                            </div>
                        </div>
                        </li>
                    <?}?>
                </ul>
                </div>
            </div>
            <div class="reviews__button">
                <a class="button button_light" href="/reviews/send/">Оставить отзыв</a>
                </div>
            </div>
    </section>
<?}?>