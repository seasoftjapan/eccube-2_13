<!--{*
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
*}-->

<!--{strip}-->
    <body bgcolor="#ffffff" text="#555555" link="#3a75af" vlink="#3a75af">
        <a name="top" id="top"></a>

        <!--{* ▼HeaderHeaderTop COLUMN *}-->
        <!--{if !empty($arrPageLayout.HeaderTopNavi)}-->
            <!--{* ▼上ナビ *}-->
            <!--{foreach key=HeaderTopNaviKey item=HeaderTopNaviItem from=$arrPageLayout.HeaderTopNavi}-->
                <!-- ▼<!--{$HeaderTopNaviItem.bloc_name}--> -->
                <!--{if $HeaderTopNaviItem.php_path != ""}-->
                    <!--{include_php_ex file=$HeaderTopNaviItem.php_path items=$HeaderTopNaviItem}-->
                <!--{else}-->
                    <!--{include file=$HeaderTopNaviItem.tpl_path items=$HeaderTopNaviItem}-->
                <!--{/if}-->
                <!-- ▲<!--{$HeaderTopNaviItem.bloc_name}--> -->
            <!--{/foreach}-->
            <!--{* ▲上ナビ *}-->
        <!--{/if}-->
        <!--{* ▲HeaderHeaderTop COLUMN *}-->

        <!--{* ▼HEADER *}-->
        <!--{if $arrPageLayout.header_chk != 2}-->
            <!--{include file= $header_tpl}-->
        <!--{/if}-->
        <!--{* ▲HEADER *}-->

        <!--{* ▼TOP COLUMN *}-->
        <!--{if !empty($arrPageLayout.TopNavi)}-->
            <!--{* ▼上ナビ *}-->
            <!--{foreach key=TopNaviKey item=TopNaviItem from=$arrPageLayout.TopNavi}-->
                <!-- ▼<!--{$TopNaviItem.bloc_name}--> -->
                <!--{if $TopNaviItem.php_path != ""}-->
                    <!--{include_php_ex file=$TopNaviItem.php_path items=$TopNaviItem}-->
                <!--{else}-->
                    <!--{include file=$TopNaviItem.tpl_path items=$TopNaviItem}-->
                <!--{/if}-->
                <!-- ▲<!--{$TopNaviItem.bloc_name}--> -->
            <!--{/foreach}-->
            <!--{* ▲上ナビ *}-->
        <!--{/if}-->
        <!--{* ▲TOP COLUMN *}-->

        <!--{* ▼メイン上部 *}-->
        <!--{if !empty($arrPageLayout.MainHead)}-->
            <!--{foreach key=MainHeadKey item=MainHeadItem from=$arrPageLayout.MainHead}-->
                <!-- ▼<!--{$MainHeadItem.bloc_name}--> -->
                <!--{if $MainHeadItem.php_path != ""}-->
                    <!--{include_php_ex file=$MainHeadItem.php_path items=$MainHeadItem}-->
                <!--{else}-->
                    <!--{include file=$MainHeadItem.tpl_path items=$MainHeadItem}-->
                <!--{/if}-->
                <!-- ▲<!--{$MainHeadItem.bloc_name}--> -->
            <!--{/foreach}-->
        <!--{/if}-->
        <!--{* ▲メイン上部 *}-->

        <!--▼MAIN-->
        <!--{include file=$tpl_mainpage}-->
        <!--▲MAIN-->

        <!--{* ▼メイン下部 *}-->
        <!--{if !empty($arrPageLayout.MainFoot)}-->
            <!--{foreach key=MainFootKey item=MainFootItem from=$arrPageLayout.MainFoot}-->
                <!-- ▼<!--{$MainFootItem.bloc_name}--> -->
                <!--{if $MainFootItem.php_path != ""}-->
                    <!--{include_php_ex file=$MainFootItem.php_path items=$MainFootItem}-->
                <!--{else}-->
                    <!--{include file=$MainFootItem.tpl_path items=$MainFootItem}-->
                <!--{/if}-->
                <!-- ▲<!--{$MainFootItem.bloc_name}--> -->
            <!--{/foreach}-->
            <!--{/if}-->
        <!--{* ▲メイン下部 *}-->

        <!--{* ▼BOTTOM COLUMN*}-->
        <!--{if !empty($arrPageLayout.BottomNavi)}-->
            <!--{* ▼下ナビ *}-->
            <!--{foreach key=BottomNaviKey item=BottomNaviItem from=$arrPageLayout.BottomNavi}-->
                <!-- ▼<!--{$BottomNaviItem.bloc_name}--> -->
                <!--{if $BottomNaviItem.php_path != ""}-->
                    <!--{include_php_ex file=$BottomNaviItem.php_path items=$BottomNaviItem}-->
                <!--{else}-->
                    <!--{include file=$BottomNaviItem.tpl_path items=$BottomNaviItem}-->
                <!--{/if}-->
                <!-- ▲<!--{$BottomNaviItem.bloc_name}--> -->
            <!--{/foreach}-->
            <!--{* ▲下ナビ *}-->
        <!--{/if}-->
        <!--{* ▲BOTTOM COLUMN*}-->

        <!--{* ▼FOOTER *}-->
        <!--{if $arrPageLayout.footer_chk != 2}-->
            <!--{include file=$footer_tpl}-->
        <!--{/if}-->
        <!--{* ▲FOOTER *}-->

        <!--{* ▼FooterBottom COLUMN *}-->
        <!--{if !empty($arrPageLayout.FooterBottomNavi)}-->
            <!--{* ▼上ナビ *}-->
            <!--{foreach key=FooterBottomNaviKey item=FooterBottomNaviItem from=$arrPageLayout.FooterBottomNavi}-->
                <!-- ▼<!--{$FooterBottomNaviItem.bloc_name}--> -->
                <!--{if $FooterBottomNaviItem.php_path != ""}-->
                    <!--{include_php_ex file=$FooterBottomNaviItem.php_path items=$BottomNaviItem}-->
                <!--{else}-->
                    <!--{include file=$FooterBottomNaviItem.tpl_path items=$FooterBottomNaviItem}-->
                <!--{/if}-->
                <!-- ▲<!--{$FooterBottomNaviItem.bloc_name}--> -->
            <!--{/foreach}-->
            <!--{* ▲上ナビ *}-->
        <!--{/if}-->
        <!--{* ▲FooterBottom COLUMN *}-->
    </body>
<!--{/strip}-->
