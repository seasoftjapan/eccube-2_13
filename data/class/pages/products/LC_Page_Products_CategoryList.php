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
 * カテゴリ一覧 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Products_CategoryList extends LC_Page_Ex
{
    /** @var array */
    public $arrCategory;
    /** @var array */
    public $arrChildren;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        parent::process();
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のAction
     *
     * @return void
     */
    public function action()
    {
        $objFormParam = $this->lfInitParam($_REQUEST);

        // カテゴリIDの正当性チェック
        $category_id = $this->lfCheckCategoryId($objFormParam->getValue('category_id'));
        if ($category_id == 0) {
            SC_Utils_Ex::sfDispSiteError(CATEGORY_NOT_FOUND);
        }

        // カテゴリ情報を取得する。
        $arrCategoryData = $this->lfGetCategories($category_id, true);
        $this->arrCategory = $arrCategoryData['arrCategory'];
        $this->arrChildren = $arrCategoryData['arrChildren'];
        $this->tpl_subtitle = $this->arrCategory['category_name'];
    }

    /* カテゴリIDの正当性チェック */

    /**
     * @return string
     */
    public function lfCheckCategoryId($category_id)
    {
        if ($category_id && !SC_Helper_DB_Ex::sfIsRecord('dtb_category', 'category_id', (array) $category_id, 'del_flg = 0')) {
            return 0;
        }

        return $category_id;
    }

    /**
     * 選択されたカテゴリとその子カテゴリの情報を取得し、
     * ページオブジェクトに格納する。
     *
     * @param  string  $category_id カテゴリID
     * @param  bool $count_check 有効な商品がないカテゴリを除くかどうか
     *
     * @return array
     */
    public function lfGetCategories($category_id, $count_check = false)
    {
        $arrCategory = null;    // 選択されたカテゴリ
        $arrChildren = []; // 子カテゴリ

        $arrAll = SC_Helper_DB_Ex::sfGetCatTree($category_id, $count_check);
        foreach ($arrAll as $category) {
            // 選択されたカテゴリの場合
            if ($category['category_id'] == $category_id) {
                $arrCategory = $category;
                continue;
            }

            // 関係のないカテゴリはスキップする。
            if ($category['parent_category_id'] != $category_id) {
                continue;
            }

            // 子カテゴリの場合は、孫カテゴリが存在するかどうかを調べる。
            $arrGrandchildrenID = SC_Utils_Ex::sfGetUnderChildrenArray($arrAll, 'parent_category_id', 'category_id', $category['category_id']);
            $category['has_children'] = count($arrGrandchildrenID) > 0;
            $arrChildren[] = $category;
        }

        if (!isset($arrCategory)) {
            SC_Utils_Ex::sfDispSiteError(CATEGORY_NOT_FOUND);
        }

        // 子カテゴリの商品数を合計する。
        $children_product_count = 0;
        foreach ($arrChildren as $category) {
            $children_product_count += $category['product_count'];
        }

        // 選択されたカテゴリに直属の商品がある場合は、子カテゴリの先頭に追加する。
        if ($arrCategory['product_count'] > $children_product_count) {
            $arrCategory['product_count'] -= $children_product_count; // 子カテゴリの商品数を除く。
            $arrCategory['has_children'] = false; // 商品一覧ページに遷移させるため。
            array_unshift($arrChildren, $arrCategory);
        }

        return ['arrChildren' => $arrChildren, 'arrCategory' => $arrCategory];
    }

    /**
     * ユーザ入力値の処理
     *
     * @return SC_FormParam_Ex
     */
    public function lfInitParam($arrRequest)
    {
        $objFormParam = new SC_FormParam_Ex();
        $objFormParam->addParam('カテゴリID', 'category_id', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        // 値の取得
        $objFormParam->setParam($arrRequest);
        // 入力値の変換
        $objFormParam->convParam();

        return $objFormParam;
    }
}
