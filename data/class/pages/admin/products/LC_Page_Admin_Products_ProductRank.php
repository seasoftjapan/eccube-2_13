<?php
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

/**
 * 商品並べ替え のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Admin_Products_ProductRank extends LC_Page_Admin_Ex
{
    /** @var int */
    public $tpl_start_row;
    /** @var int */
    public $tpl_disppage;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = 'products/product_rank.tpl';
        $this->tpl_mainno = 'products';
        $this->tpl_subno = 'product_rank';
        $this->tpl_maintitle = '商品管理';
        $this->tpl_subtitle = '商品並び替え';
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        $objDb = new SC_Helper_DB_Ex();
        $objCategory = new SC_Helper_Category_Ex();

        $this->tpl_pageno = $_POST['pageno'] ?? '';

        // 通常時は親カテゴリを0に設定する。
        $this->arrForm['parent_category_id'] =
            $_POST['parent_category_id'] ?? 0;
        $this->arrForm['product_id'] =
            $_POST['product_id'] ?? '';

        switch ($this->getMode()) {
            case 'up':
                $this->lfRankUp($objDb, $this->arrForm['parent_category_id'], $this->arrForm['product_id']);
                break;
            case 'down':
                $this->lfRankDown($objDb, $this->arrForm['parent_category_id'], $this->arrForm['product_id']);
                break;
            case 'move':
                $this->lfRankMove($objDb, $this->arrForm['parent_category_id'], $this->arrForm['product_id']);
                break;
            case 'tree':
                // カテゴリの切替は、ページ番号をクリアする。
                $this->tpl_pageno = '';
                break;
            case 'renumber':
                $this->lfRenumber($this->arrForm['parent_category_id']);
                break;
            default:
                break;
        }

        $this->arrTree = $objCategory->getTree();
        $this->arrParentID = $objCategory->getTreeTrail($this->arrForm['parent_category_id']);
        $this->arrProductsList = $this->lfGetProduct($this->arrForm['parent_category_id']);
        $arrBread = $objCategory->getTreeTrail($this->arrForm['parent_category_id'], false);
        $this->tpl_bread_crumbs = SC_Utils_Ex::jsonEncode(array_reverse($arrBread));
    }

    /* 商品読み込み */
    public function lfGetProduct($category_id)
    {
        // FIXME SC_Product クラスを使用した実装
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $col = 'alldtl.product_id, name, main_list_image, product_code_min, product_code_max, status';
        $objProduct = new SC_Product_Ex();
        $table = $objProduct->alldtlSQL();
        $table .= ' LEFT JOIN dtb_product_categories AS T5 ON alldtl.product_id = T5.product_id';
        $where = 'del_flg = 0 AND category_id = ?';

        // 行数の取得
        $linemax = $objQuery->count($table, $where, [$category_id]);
        // 該当件数表示用
        $this->tpl_linemax = $linemax;

        $objNavi = new SC_PageNavi_Ex($this->tpl_pageno, $linemax, SEARCH_PMAX, 'eccube.movePage', NAVI_PMAX);
        $startno = $objNavi->start_row;
        $this->tpl_start_row = $objNavi->start_row;
        $this->tpl_strnavi = $objNavi->strnavi;     // Navi表示文字列
        $this->tpl_pagemax = $objNavi->max_page;    // ページ最大数（「上へ下へ」表示判定用）
        $this->tpl_disppage = $objNavi->now_page;   // 表示ページ番号（「上へ下へ」表示判定用）

        // 取得範囲の指定(開始行番号、行数のセット)
        $objQuery->setLimitOffset(SEARCH_PMAX, $startno);

        $objQuery->setOrder('rank DESC, alldtl.product_id DESC');

        $arrRet = $objQuery->select($col, $table, $where, [$category_id]);

        return $arrRet;
    }

    /*
     * 商品の数値指定での並び替え実行
     */
    public function lfRenumber($parent_category_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $sql = <<< __EOS__
            UPDATE dtb_product_categories
            SET
                rank =
                    (
                        SELECT COUNT(*)
                        FROM (SELECT product_id,rank FROM dtb_product_categories WHERE category_id = dtb_product_categories.category_id) t_in
                        WHERE
                            t_in.rank < dtb_product_categories.rank
                            OR (
                                t_in.rank = dtb_product_categories.rank
                                AND t_in.product_id < dtb_product_categories.product_id
                            )
                    ) + 1
            WHERE dtb_product_categories.category_id = ?
            __EOS__;

        $arrRet = $objQuery->query($sql, [$parent_category_id]);

        return $arrRet;
    }

    /**
     * @param SC_Helper_DB_Ex $objDb
     */
    public function lfRankUp(&$objDb, $parent_category_id, $product_id)
    {
        $where = 'category_id = '.SC_Utils_Ex::sfQuoteSmart($parent_category_id);
        $objDb->sfRankUp('dtb_product_categories', 'product_id', $product_id, $where);
    }

    /**
     * @param SC_Helper_DB_Ex $objDb
     */
    public function lfRankDown(&$objDb, $parent_category_id, $product_id)
    {
        $where = 'category_id = '.SC_Utils_Ex::sfQuoteSmart($parent_category_id);
        $objDb->sfRankDown('dtb_product_categories', 'product_id', $product_id, $where);
    }

    /**
     * @param SC_Helper_DB_Ex $objDb
     */
    public function lfRankMove(&$objDb, $parent_category_id, $product_id)
    {
        $key = 'pos-'.$product_id;
        $input_pos = mb_convert_kana($_POST[$key], 'n');
        if (SC_Utils_Ex::sfIsInt($input_pos)) {
            $where = 'category_id = '.SC_Utils_Ex::sfQuoteSmart($parent_category_id);
            $objDb->sfMoveRank('dtb_product_categories', 'product_id', $product_id, $input_pos, $where);
        }
    }
}
