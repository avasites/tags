<?php 

use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyIndex;
use Bitrix\Iblock\SectionTable;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class TagsComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $result = array(
            "CACHE_TYPE"            => $arParams["CACHE_TYPE"],
            "CACHE_TIME"            => isset($arParams["CACHE_TIME"]) ?
                                            $arParams["CACHE_TIME"]: 
                                            36000000,
            "PROPS"                 => $arParams["PROPS"],
            "IBLOCK_ID"             => intval($arParams['IBLOCK_ID']),
            "SECTION"               => $arParams['SECTION'],
            "HIDE_NOT_AVAILABLE"    => $arParams['HIDE_NOT_AVAILABLE'],
            "TEMPLATE_URL"          => isset($arParams['TEMPLATE_URL']) ? 
                                            mb_strtolower($arParams['TEMPLATE_URL']) : 
                                            '/catalog/#SECTION#/#PROP_CODE#/',
        );
        return $result;
    }

    public function executeComponent()
    {
        $nameSection = '';

        if(!Loader::includeModule('iblock'))
            return false;

        if($this->StartResultCache($this->arParams["CACHE_TIME"])){


            $arFilterSection[(intval($this->arParams['SECTION']) > 0 ? 'ID' : 'CODE')] = $this->arParams['SECTION'];
                
            $resSectionData = SectionTable::getList([
                'select'=>['CODE', 'NAME'],
                'filter'=>[
                    $arFilterSection
                ],
                'limit' => 1
            ]);

            if($arSectionData = $resSectionData->Fetch())
            {
                $nameSection = $arSectionData['NAME'];

                $this->arParams['TEMPLATE_URL'] = strtr($this->arParams['TEMPLATE_URL'], 
                    [
                        '#SECTION#' => $codeSection
                    ]
                );
            }

            $arFilter = [
                '=SECTION_ID'=> $this->arParams['SECTION'],
                'INCLUDE_SUBSECTIONS' => 'Y',
                'ACTIVE' => 'Y', 
                'SECTION_SCOPE' => 'IBLOCK',
            ];

            if($this->arParams['HIDE_NOT_AVAILABLE'] == 'Y')
                $arFilter['CATALOG_AVAILABLE'] = 'Y';

            $resToProprty = PropertyTable::getList([
                'select' => ['CODE', 'PROPERTY_TYPE', 'ID', 'LINK_IBLOCK_ID', 'NAME'],
                'filter' => ['IBLOCK_ID'=>$this->arParams['IBLOCK_ID'], 'ID' => $this->arParams['PROPS']]
            ]);

            while($arProperty = $resToProprty->Fetch())
            {

                $iterator = CIBlockElement::GetPropertyValues($this->arParams['IBLOCK_ID'], $arFilter, false, ['ID'=>[$arProperty['ID']]]);

                $arPropValueID = [];

                while ($row = $iterator->Fetch())
                {
                    if(!empty($row[$arProperty['ID']]) && !in_array($row[$arProperty['ID']], $arPropValueID))
                        $arPropValueID[] = $row[$arProperty['ID']];
                }

                $this->arResult["TAGS"][$arProperty['CODE']]['CODE'] = $arProperty['CODE'];
                $this->arResult["TAGS"][$arProperty['CODE']]['NAME'] = $arProperty['NAME'];

                switch($arProperty['PROPERTY_TYPE']){

                    case 'L': //Свойство типа "Список"
                    
                        $resEnumProp = PropertyEnumerationTable::getList([
                            'select'=>['*'],
                            'filter' => [
                                'PROPERTY_ID'=> $arProperty['ID'], 
                                'ID'=>$arPropValueID
                            ]
                        ]);
                        
                        while($arEnum = $resEnumProp->Fetch())
                        {
                            if(!empty($arEnum))
                            {
                                $this->arResult["TAGS"][$arProperty['CODE']]['VALUE'][$arEnum["ID"]]['NAME'] = $arEnum['VALUE']; 
                                $this->arResult["TAGS"][$arProperty['CODE']]['VALUE'][$arEnum["ID"]]['URL'] = strtr($this->arParams['TEMPLATE_URL'], ['#PROP_CODE#' => $arEnum['XML_ID']]); 
                            
                            }
                                
                        }

                        break;

                    case 'E': //Свойство типа "Привязка к эелементам"
                        
                        $getValueProperty = ElementTable::getList([
                            'select' => ['NAME','CODE', 'ID'],
                            'filter' => ['IBLOCK_ID'=> $arProperty['LINK_IBLOCK_ID'], 'ACTIVE'=>'Y', '=ID'=>$arPropValueID],
                            'order'  => ['NAME'=>'ASC']
                        ]);

                        while($arValueProperty = $getValueProperty->Fetch())
                        {
                            $this->arResult["TAGS"][$arProperty['CODE']]['VALUE'][$arValueProperty["ID"]] = $arValueProperty; 
                            $this->arResult["TAGS"][$arProperty['CODE']]['VALUE'][$arValueProperty["ID"]]['NAME'] = $arValueProperty['NAME']; 
                            $this->arResult["TAGS"][$arProperty['CODE']]['VALUE'][$arValueProperty["ID"]]['URL'] = strtr($this->arParams['TEMPLATE_URL'], 
                                ['#PROP_CODE#' => $arValueProperty['CODE']
                            ]);
                        }

                        break;

                }

            
            }

            $this->includeComponentTemplate();


        }
    
        

    }

}