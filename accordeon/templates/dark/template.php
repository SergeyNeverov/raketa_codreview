<? 
$Helper = new Helper;
// $Helper->debuger($arResult['ITEMS']);
if(!empty($arResult['ITEMS'])){?>
    <section class="section prices prices_dark">
        <div class="container prices__container prices__container_dark">
            <h2 class="title title_section title_light"><?=$arParams['TITLE']?></h2>
            <div class="accordeon">
                <ul class="accordeon__list accordeon__list_dark">
                    <? 
                    $i = 0;
                    foreach($arResult['ITEMS'] as $item){?>
                        <li class="accordeon__item">
                            <div class="accordeon-item accordeon-item_dark">
                                <a class="accordeon-item__button accordeon-item__button_small <? if($i == 0){?>open<?}?>" href="javascript:void(0);" data-accordeon="">
                                    <p class="accordeon-item__button-title"><?=$item['NAME'];?></p>
                                    <p class="accordeon-item__button-price">от  <?=number_format($item['MIN_PRICE'], 0, '', ' ');?> 
                                        <svg>
                                            <use xlink:href="<?=$Helper::assetsPath();?>/img/symbols.svg#svg-currency"></use>
                                        </svg>
                                    </p>
                                    <span class="accordeon-item__button-text" data-change-text=""><?if($i == 0){ echo 'Свернуть';} else {echo 'Показать цены';}?></span>
                                </a>
                                <div class="accordeon-item__content <? if($i == 0){?>open<?}?>">
                                    <ul class="accordeon-item__list">
                                        <? foreach($item['SUBITEMS'] as $subItem){?>
                                            <li class="accordeon-item__item">
                                                <a class="accordeon-link accordeon-link_dark" href="javascript:void(0)" data-modal-open="request" data-service="<?=$subItem['ID'];?>" data-title="<?=$subItem['NAME'];?>">
                                                    <span class="accordeon-link__title"><?=$subItem['NAME']?></span>
                                                    <? if(!empty($subItem['OLD_PRICE'])){?>
                                                        <span class="accordeon-link__oldprice"><?=$subItem['OLD_PRICE'];?>
                                                            <svg>
                                                                <use xlink:href="<?=$Helper::assetsPath();?>/img/symbols.svg#svg-currency"></use>
                                                            </svg>
                                                        </span>
                                                    <?}?>
                                                    <span class="accordeon-link__price"><?=$subItem['PRICE']?> 
                                                        <svg>
                                                            <use xlink:href="<?=$Helper::assetsPath();?>/img/symbols.svg#svg-currency"></use>
                                                        </svg>
                                                    </span>
                                                    <span class="accordeon-link__text">Записаться</span>
                                                </a>
                                            </li>
                                        <?}?>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    <?
                    $i++;
                    }?>
                </ul>
            </div>
            <? if($arParams['SHOW_BUTTON'] != 'N'){?>
                <div class="prices__button">
                    <a class="button button_light button_light-width" href="/prices/">Подробнее</a>
                </div>
            <?}?>
        </div>
    </section>
<?}?>