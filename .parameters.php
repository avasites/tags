<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;

$arIBlocks = $arIBlockProps = [];

if(Loader::includeModule('iblock'))
{
    $resGetIBlocks = IblockTable::getList([
        'select' => ['NAME', 'ID']
    ]);

    while($arIBlock = $resGetIBlocks->Fetch())
    {
        $arIBlocks[$arIBlock['ID']] = "[".$arIBlock['ID']."] ".$arIBlock['NAME'];
    }

    foreach(CIBlockSectionPropertyLink::GetArray($arCurrentValues['IBLOCK_ID'], 0) as $props)
            {
                if($props['SMART_FILTER'] == 'Y')
                {
                    $arListProps[] = $props['PROPERTY_ID'];
                }
            }

    $resGetIBlockProps = PropertyTable::getList([
        'select' => ['NAME', 'CODE', 'ID'],
        'filter' => ['IBLOCK_ID'=> $arCurrentValues['IBLOCK_ID'], 'ID'=>$arListProps]
    ]);

    while($arIBlockProp = $resGetIBlockProps->Fetch())
    {
        $arIBlockProps[$arIBlockProp['ID']] = "[".$arIBlockProp['CODE']."] ".$arIBlockProp['NAME'];
    }
}

$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "IBLOCK_ID" => [
            "NAME" => GetMessage("INFOBLOCK_ID_PHR"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "REFRESH" => 'Y'
        ],
        "PROPS" => [
            "NAME" => GetMessage("INFOBLOCK_PROP_PHR"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlockProps,
            "REFRESH" => 'Y',
            "MULTIPLE" => "Y"
        ],
        "SECTION" => [
            "TYPE" => "STRING",
            "NAME" => GetMessage("SECTION_PHR"),
        ],
        'TEMPLATE_URL' => [
            "TYPE" => "STRING",
            "NAME" => GetMessage("TEMPLATE_URL_PHR"),
            'DEFAULT' => '/catalog/#SECTION#/#PROP_CODE#/'
        ],
        'HIDE_NOT_AVAILABLE' => [
            "TYPE" => "LIST",
            "NAME" => GetMessage("HIDE_NOT_AVAILABLE_PHR"),
            "VALUES" => [
                'Y' => GetMessage("HIDE_NOT_AVAILABLE_Y_PHR"),
                'N' => GetMessage("HIDE_NOT_AVAILABLE_N_PHR"),
            ]
        ],
        "CACHE_TIME" => [],
    ),
);
?>