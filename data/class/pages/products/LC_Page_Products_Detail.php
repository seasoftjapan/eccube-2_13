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
 * 商品詳細 のページクラス.
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class LC_Page_Products_Detail extends LC_Page_Ex
{
    /** 商品ステータス */
    public $arrSTATUS;

    /** 商品ステータス画像 */
    public $arrSTATUS_IMAGE;

    /** 発送予定日 */
    public $arrDELIVERYDATE;

    /** おすすめレベル */
    public $arrRECOMMEND;

    /** @var SC_FormParam フォームパラメーター */
    public $objFormParam;

    /** @var SC_UploadFile アップロードファイル */
    public $objUpFile;

    /** @var string モード */
    public $mode;

    /** @var array 商品情報 */
    public $arrProduct;

    /** @var string 規格1クラス名 */
    public $tpl_class_name1;

    /** @var string 規格2クラス名 */
    public $tpl_class_name2;

    /** @var bool 在庫があるかどうか */
    public $tpl_stock_find;

    /** @var array 規格1の規格分類 */
    public $arrClassCat1;

    /** @var bool 規格1が設定されている */
    public $tpl_classcat_find1;

    /** @var bool 規格2が設定されている */
    public $tpl_classcat_find2;

    /** @var int デフォルトの商品規格ID */
    public $tpl_product_class_id;

    /** @var int デフォルトの商品タイプ */
    public $tpl_product_type;

    /** @var string ページ表示時に実行するJavaScript */
    public $js_lnOnload;

    /** @var int 商品ID */
    public $tpl_product_id;

    /** @var array フォーム情報 */
    public $arrForm;

    /** @var string サブタイトル */
    public $tpl_subtitle;

    /** @var array 関連カテゴリー */
    public $arrRelativeCat;

    /** @var array 商品ステータス（アイコン） */
    public $productStatus;

    /** @var bool サブ画像が存在するか */
    public $subImageFlag;

    /** @var array レビュー情報 */
    public $arrReview;

    /** @var array 関連商品情報 */
    public $arrRecommend;

    /** @var array ファイル情報 */
    public $arrFile;

    /** @var bool ログイン状態かどうか */
    public $tpl_login;

    /** @var bool お気に入りに登録済みか */
    public $is_favorite;

    /** @var bool お気に入りに登録したことを示すフラグ */
    public $just_added_favorite;

    /** @var array エラー情報 */
    public $arrErr;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $masterData = new SC_DB_MasterData_Ex();
        $this->arrSTATUS = $masterData->getMasterData('mtb_status');
        $this->arrSTATUS_IMAGE = $masterData->getMasterData('mtb_status_image');
        $this->arrDELIVERYDATE = $masterData->getMasterData('mtb_delivery_date');
        $this->arrRECOMMEND = $masterData->getMasterData('mtb_recommend');

        // POST に限定する mode
        $this->arrLimitPostMode[] = 'cart';
        $this->arrLimitPostMode[] = 'add_favorite';
        $this->arrLimitPostMode[] = 'add_favorite_sphone';
        $this->arrLimitPostMode[] = 'select';
        $this->arrLimitPostMode[] = 'select2';
        $this->arrLimitPostMode[] = 'selectItem';
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
     * Page のAction.
     *
     * @return void
     */
    public function action()
    {
        // 決済処理中ステータスのロールバック
        $objPurchase = new SC_Helper_Purchase_Ex();
        $objPurchase->cancelPendingOrder(PENDING_ORDER_CANCEL_FLAG);

        // 会員クラス
        $objCustomer = new SC_Customer_Ex();

        // パラメーター管理クラス
        $this->objFormParam = new SC_FormParam_Ex();
        // パラメーター情報の初期化
        $this->arrForm = $this->lfInitParam($this->objFormParam);
        // ファイル管理クラス
        $this->objUpFile = new SC_UploadFile_Ex(IMAGE_TEMP_REALDIR, IMAGE_SAVE_REALDIR);
        // ファイル情報の初期化
        $this->objUpFile = $this->lfInitFile($this->objUpFile);
        $this->mode = $this->getMode();

        $objProduct = new SC_Product_Ex();

        // プロダクトIDの正当性チェック
        $product_id = $this->lfCheckProductId($this->objFormParam->getValue('admin'), $this->objFormParam->getValue('product_id'), $objProduct);

        $objProduct->setProductsClassByProductIds([$product_id]);

        // 規格1クラス名
        $this->tpl_class_name1 = $objProduct->className1[$product_id];

        // 規格2クラス名
        $this->tpl_class_name2 = $objProduct->className2[$product_id];

        // 規格1
        $this->arrClassCat1 = $objProduct->classCats1[$product_id];

        // 規格1が設定されている
        $this->tpl_classcat_find1 = $objProduct->classCat1_find[$product_id];
        // 規格2が設定されている
        $this->tpl_classcat_find2 = $objProduct->classCat2_find[$product_id];

        $this->tpl_stock_find = $objProduct->stock_find[$product_id];
        $this->tpl_product_class_id = $objProduct->classCategories[$product_id]['__unselected']['__unselected']['product_class_id'];
        $this->tpl_product_type = $objProduct->classCategories[$product_id]['__unselected']['__unselected']['product_type'];

        // 在庫が無い場合は、OnLoadしない。(javascriptエラー防止)
        if ($this->tpl_stock_find) {
            // 規格選択セレクトボックスの作成
            $this->js_lnOnload .= $this->lfMakeSelect();
        }

        $this->tpl_javascript .= 'eccube.classCategories = '.SC_Utils_Ex::jsonEncode($objProduct->classCategories[$product_id]).';';
        $this->tpl_javascript .= 'function lnOnLoad()
        {'.$this->js_lnOnload.'}';
        $this->tpl_onload .= 'lnOnLoad();';

        // モバイル用 規格選択セレクトボックスの作成
        if (SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE) {
            $this->lfMakeSelectMobile($this, $product_id, $this->objFormParam->getValue('classcategory_id1'));
        }

        // 商品IDをFORM内に保持する
        $this->tpl_product_id = $product_id;

        switch ($this->mode) {
            case 'cart':
                $this->doCart();
                break;

            case 'add_favorite':
                $this->doAddFavorite($objCustomer);
                break;

            case 'add_favorite_sphone':
                $this->doAddFavoriteSphone($objCustomer);
                break;

            case 'select':
            case 'select2':
            case 'selectItem':
                // nop
                break;

            default:
                $this->doDefault();
                break;
        }

        // モバイル用 ポストバック処理
        if (SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE) {
            switch ($this->mode) {
                case 'select':
                    $this->doMobileSelect();
                    break;

                case 'select2':
                    $this->doMobileSelect2();
                    break;

                case 'selectItem':
                    $this->doMobileSelectItem();
                    break;

                case 'cart':
                    $this->doMobileCart();
                    break;

                default:
                    $this->doMobileDefault();
                    break;
            }
        }

        // 商品詳細を取得
        $this->arrProduct = $objProduct->getDetail($product_id);

        // サブタイトルを取得
        $this->tpl_subtitle = $this->arrProduct['name'];

        // 関連カテゴリを取得
        $this->arrRelativeCat = SC_Helper_DB_Ex::sfGetMultiCatTree($product_id);

        // 商品ステータスを取得
        $this->productStatus = $objProduct->getProductStatus([$product_id]);

        // 画像ファイル指定がない場合の置換処理
        $this->arrProduct['main_image']
            = SC_Utils_Ex::sfNoImageMain($this->arrProduct['main_image']);

        $this->subImageFlag = $this->lfSetFile($this->objUpFile, $this->arrProduct, $this->arrFile);
        // レビュー情報の取得
        $this->arrReview = $this->lfGetReviewData($product_id);

        // 関連商品情報表示
        $this->arrRecommend = $this->lfPreGetRecommendProducts($product_id);

        // ログイン判定
        if ($objCustomer->isLoginSuccess() === true) {
            // お気に入りボタン表示
            $this->tpl_login = true;
            $this->is_favorite = SC_Helper_DB_Ex::sfDataExists('dtb_customer_favorite_products', 'customer_id = ? AND product_id = ?', [$objCustomer->getValue('customer_id'), $product_id]);
        }
    }

    /**
     * プロダクトIDの正当性チェック
     *
     * @param string $admin_mode
     * @param int $product_id
     * @param SC_Product $objProduct
     *
     * @return int
     */
    public function lfCheckProductId($admin_mode, $product_id, SC_Product $objProduct)
    {
        // 管理機能からの確認の場合は、非公開の商品も表示する。
        if (!is_null($admin_mode) && $admin_mode == 'on' && SC_Utils_Ex::sfIsSuccess(new SC_Session_Ex(), false)) {
            $include_hidden = true;
        } else {
            $include_hidden = false;
        }

        if (!$objProduct->isValidProductId($product_id, $include_hidden)) {
            SC_Utils_Ex::sfDispSiteError(PRODUCT_NOT_FOUND);
        }

        return $product_id;
    }

    /**
     * ファイル情報の初期化
     *
     * @param SC_UploadFile $objUpFile
     *
     * @return SC_UploadFile
     */
    public function lfInitFile(SC_UploadFile $objUpFile)
    {
        $objUpFile->addFile('詳細-メイン画像', 'main_image', ['jpg'], IMAGE_SIZE);
        for ($cnt = 1; $cnt <= PRODUCTSUB_MAX; $cnt++) {
            $objUpFile->addFile("詳細-サブ画像$cnt", "sub_image$cnt", ['jpg'], IMAGE_SIZE);
        }

        return $objUpFile;
    }

    /* 規格選択セレクトボックスの作成 */
    public function lfMakeSelect()
    {
        return 'fnSetClassCategories('
            .'document.form1, '
            .SC_Utils_Ex::jsonEncode($this->objFormParam->getValue('classcategory_id2'))
            .'); ';
    }

    /* 規格選択セレクトボックスの作成(モバイル) */

    /**
     * @param LC_Page_Products_Detail $objPage
     * @param int $product_id
     */
    public function lfMakeSelectMobile(&$objPage, $product_id, $request_classcategory_id1)
    {
        $classcat_find1 = false;
        $classcat_find2 = false;

        // 規格名一覧
        $arrClassName = SC_Helper_DB_Ex::sfGetIDValueList('dtb_class', 'class_id', 'name');
        // 規格分類名一覧
        $arrClassCatName = SC_Helper_DB_Ex::sfGetIDValueList('dtb_classcategory', 'classcategory_id', 'name');
        // 商品規格情報の取得
        $arrProductsClass = $this->lfGetProductsClass($product_id);

        // 規格1クラス名の取得
        $objPage->tpl_class_name1 = $arrClassName[$arrProductsClass[0]['class_id1']];
        // 規格2クラス名の取得
        $objPage->tpl_class_name2 = $arrClassName[$arrProductsClass[0]['class_id2']];

        // 全ての組み合わせ数
        $count = count($arrProductsClass);

        $classcat_id1 = '';
        $classcat_id2 = '';

        $arrSele1 = [];
        $arrSele2 = [];

        for ($i = 0; $i < $count; $i++) {
            // 在庫のチェック
            if ($arrProductsClass[$i]['stock'] <= 0 && $arrProductsClass[$i]['stock_unlimited'] != '1') {
                continue;
            }

            // 規格1のセレクトボックス用
            if ($classcat_id1 != $arrProductsClass[$i]['classcategory_id1']) {
                $classcat_id1 = $arrProductsClass[$i]['classcategory_id1'];
                $arrSele1[$classcat_id1] = $arrClassCatName[$classcat_id1];
            }

            // 規格2のセレクトボックス用
            if ($arrProductsClass[$i]['classcategory_id1'] == $request_classcategory_id1 && $classcat_id2 != $arrProductsClass[$i]['classcategory_id2']) {
                $classcat_id2 = $arrProductsClass[$i]['classcategory_id2'];
                $arrSele2[$classcat_id2] = $arrClassCatName[$classcat_id2];
            }
        }

        // 規格1
        $objPage->arrClassCat1 = $arrSele1;
        $objPage->arrClassCat2 = $arrSele2;

        // 規格1が設定されている
        if (isset($arrProductsClass[0]['classcategory_id1']) && $arrProductsClass[0]['classcategory_id1'] != '0') {
            $classcat_find1 = true;
        }

        // 規格2が設定されている
        if (isset($arrProductsClass[0]['classcategory_id2']) && $arrProductsClass[0]['classcategory_id2'] != '0') {
            $classcat_find2 = true;
        }

        $objPage->tpl_classcat_find1 = $classcat_find1;
        $objPage->tpl_classcat_find2 = $classcat_find2;
    }

    /**
     * パラメーター情報の初期化
     *
     * @param SC_FormParam $objFormParam
     *
     * @return array
     */
    public function lfInitParam(SC_FormParam &$objFormParam)
    {
        $objFormParam->addParam('規格1', 'classcategory_id1', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('規格2', 'classcategory_id2', INT_LEN, 'n', ['NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('数量', 'quantity', INT_LEN, 'n', ['EXIST_CHECK', 'ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('管理者ログイン', 'admin', INT_LEN, 'a', ['ALNUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', ['EXIST_CHECK', 'ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('お気に入り商品ID', 'favorite_product_id', INT_LEN, 'n', ['ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        $objFormParam->addParam('商品規格ID', 'product_class_id', INT_LEN, 'n', ['EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK']);
        // 値の取得
        $objFormParam->setParam($_REQUEST);
        // 入力値の変換
        $objFormParam->convParam();

        // 入力情報を渡す
        return $objFormParam->getFormParamList();
    }

    /* 商品規格情報の取得 */

    /**
     * @param int $product_id
     */
    public function lfGetProductsClass($product_id)
    {
        $objProduct = new SC_Product_Ex();

        return $objProduct->getProductsClassFullByProductId($product_id);
    }

    /* 登録済み関連商品の読み込み */

    /**
     * @param int $product_id
     */
    public function lfPreGetRecommendProducts($product_id)
    {
        $objProduct = new SC_Product_Ex();
        $objQuery = SC_Query_Ex::getSingletonInstance();

        $objQuery->setOrder('rank DESC');
        $arrRecommendData = $objQuery->select('recommend_product_id, comment', 'dtb_recommend_products as t1 left join dtb_products as t2 on t1.recommend_product_id = t2.product_id', 't1.product_id = ? and t2.del_flg = 0 and t2.status = 1', [$product_id]);

        $recommendProductIds = [];
        foreach ($arrRecommendData as $recommend) {
            $recommendProductIds[] = $recommend['recommend_product_id'];
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrProducts = $objProduct->getListByProductIds($objQuery, $recommendProductIds);

        foreach ($arrRecommendData as $key => $arrRow) {
            $arrRecommendData[$key] = array_merge($arrRow, $arrProducts[$arrRow['recommend_product_id']]);
        }

        return $arrRecommendData;
    }

    /* 入力内容のチェック */

    /**
     * @param string $mode
     * @param bool $tpl_classcat_find1
     * @param bool $tpl_classcat_find2
     */
    public function lfCheckError($mode, SC_FormParam &$objFormParam, $tpl_classcat_find1 = null, $tpl_classcat_find2 = null)
    {
        switch ($mode) {
            case 'add_favorite_sphone':
            case 'add_favorite':
                $objCustomer = new SC_Customer_Ex();
                $objErr = new SC_CheckError_Ex();
                $customer_id = $objCustomer->getValue('customer_id');
                $favorite_product_id = $objFormParam->getValue('favorite_product_id');
                if (SC_Helper_DB_Ex::sfDataExists('dtb_customer_favorite_products', 'customer_id = ? AND product_id = ?', [$customer_id, $favorite_product_id])) {
                    $objErr->arrErr['add_favorite'.$favorite_product_id] = '※ この商品は既にお気に入りに追加されています。<br />';
                }
                break;
            default:
                // 入力データを渡す。
                $arrRet = $objFormParam->getHashArray();
                $objErr = new SC_CheckError_Ex($arrRet);
                $objErr->arrErr = $objFormParam->checkError();

                // 複数項目チェック
                if ($tpl_classcat_find1) {
                    $objErr->doFunc(['規格1', 'classcategory_id1'], ['EXIST_CHECK']);
                }
                if ($tpl_classcat_find2) {
                    $objErr->doFunc(['規格2', 'classcategory_id2'], ['EXIST_CHECK']);
                }
                break;
        }

        return $objErr->arrErr;
    }

    /**
     * 商品ごとのレビュー情報を取得する
     *
     * @param int $product_id
     *
     * @return array
     */
    public function lfGetReviewData($product_id)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        // 商品ごとのレビュー情報を取得する
        $col = 'create_date, reviewer_url, reviewer_name, recommend_level, title, comment';
        $from = 'dtb_review';
        $where = 'del_flg = 0 AND status = 1 AND product_id = ?';
        $objQuery->setOrder('create_date DESC');
        $objQuery->setLimit(REVIEW_REGIST_MAX);
        $arrWhereVal = [$product_id];
        $arrReview = $objQuery->select($col, $from, $where, $arrWhereVal);

        return $arrReview;
    }

    /**
     * ファイルの情報をセットする
     *
     * @param SC_UploadFile $objUpFile
     * @param array $arrProduct
     * @param array $arrFile
     *
     * @return bool
     */
    public function lfSetFile(SC_UploadFile $objUpFile, $arrProduct, &$arrFile)
    {
        // DBからのデータを引き継ぐ
        $objUpFile->setDBFileList($arrProduct);
        // ファイル表示用配列を渡す
        $arrFile = $objUpFile->getFormFileList(null, null, true);

        // サブ画像の有無を判定
        $subImageFlag = false;
        for ($i = 1; $i <= PRODUCTSUB_MAX; $i++) {
            if (isset($arrFile['sub_image'.$i]['filepath'])) {
                $subImageFlag = true;
            }
        }

        return $subImageFlag;
    }

    /*
     * お気に入り商品登録
     * @return void
     */
    public function lfRegistFavoriteProduct($favorite_product_id, $customer_id)
    {
        // ログイン中のユーザが商品をお気に入りにいれる処理
        if (!SC_Helper_DB_Ex::sfIsRecord('dtb_products', 'product_id', $favorite_product_id, 'del_flg = 0 AND status = 1')) {
            SC_Utils_Ex::sfDispSiteError(PRODUCT_NOT_FOUND);

            return false;
        } else {
            $objQuery = SC_Query_Ex::getSingletonInstance();
            $exists = $objQuery->exists('dtb_customer_favorite_products', 'customer_id = ? AND product_id = ?', [$customer_id, $favorite_product_id]);

            if (!$exists) {
                $sqlval['customer_id'] = $customer_id;
                $sqlval['product_id'] = $favorite_product_id;
                $sqlval['update_date'] = 'CURRENT_TIMESTAMP';
                $sqlval['create_date'] = 'CURRENT_TIMESTAMP';

                $objQuery->begin();
                $objQuery->insert('dtb_customer_favorite_products', $sqlval);
                $objQuery->commit();
            }
            // お気に入りに登録したことを示すフラグ
            $this->just_added_favorite = true;

            return true;
        }
    }

    /**
     * Add product(s) into the cart.
     *
     * @return void
     */
    public function doCart()
    {
        $this->arrErr = $this->lfCheckError(
            $this->mode,
            $this->objFormParam,
            $this->tpl_classcat_find1,
            $this->tpl_classcat_find2
        );
        if (count($this->arrErr) == 0) {
            $objCartSess = new SC_CartSession_Ex();
            $product_class_id = $this->objFormParam->getValue('product_class_id');

            $objCartSess->addProduct($product_class_id, $this->objFormParam->getValue('quantity'));

            // 開いているカテゴリーツリーを維持するためのパラメーター
            $arrQueryString = [
                'product_id' => $this->objFormParam->getValue('product_id'),
            ];

            SC_Response_Ex::sendRedirect(CART_URL, $arrQueryString);
            SC_Response_Ex::actionExit();
        }
    }

    /**
     * Add product to authenticated user's favorites.
     *
     * @param  SC_Customer $objCustomer
     *
     * @return void
     */
    public function doAddFavorite(SC_Customer &$objCustomer)
    {
        // ログイン中のユーザが商品をお気に入りにいれる処理
        if ($objCustomer->isLoginSuccess() === true && $this->objFormParam->getValue('favorite_product_id') > 0) {
            $this->arrErr = $this->lfCheckError($this->mode, $this->objFormParam);
            if (count($this->arrErr) == 0) {
                if (!$this->lfRegistFavoriteProduct($this->objFormParam->getValue('favorite_product_id'), $objCustomer->getValue('customer_id'))) {
                    SC_Response_Ex::actionExit();
                }
                $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();
                $objPlugin->doAction('LC_Page_Products_Detail_action_add_favorite', [$this]);
            }
        }
    }

    /**
     * Add product to authenticated user's favorites. (for Smart phone)
     *
     * @param  SC_Customer $objCustomer
     *
     * @return void
     */
    public function doAddFavoriteSphone(SC_Customer $objCustomer)
    {
        // ログイン中のユーザが商品をお気に入りにいれる処理(スマートフォン用)
        if ($objCustomer->isLoginSuccess() === true && $this->objFormParam->getValue('favorite_product_id') > 0) {
            $this->arrErr = $this->lfCheckError($this->mode, $this->objFormParam);
            if (count($this->arrErr) == 0) {
                if ($this->lfRegistFavoriteProduct($this->objFormParam->getValue('favorite_product_id'), $objCustomer->getValue('customer_id'))) {
                    $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();
                    $objPlugin->doAction('LC_Page_Products_Detail_action_add_favorite_sphone', [$this]);
                    echo 'true';
                    SC_Response_Ex::actionExit();
                }
            }
            echo 'error';
            SC_Response_Ex::actionExit();
        }
    }

    /**
     * @return void
     */
    public function doDefault()
    {
    }

    /**
     * @return void
     */
    public function doMobileSelect()
    {
        // 規格1が設定されている場合
        if ($this->tpl_classcat_find1) {
            // templateの変更
            $this->tpl_mainpage = 'products/select_find1.tpl';

            return;
        }

        // 数量の入力を行う
        $this->tpl_mainpage = 'products/select_item.tpl';
    }

    /**
     * @return void
     */
    public function doMobileSelect2()
    {
        $this->arrErr = $this->lfCheckError($this->mode, $this->objFormParam, $this->tpl_classcat_find1, $this->tpl_classcat_find2);

        // 規格1が設定されていて、エラーを検出した場合
        if ($this->tpl_classcat_find1 && $this->arrErr['classcategory_id1']) {
            // templateの変更
            $this->tpl_mainpage = 'products/select_find1.tpl';

            return;
        }

        // 規格2が設定されている場合
        if ($this->tpl_classcat_find2) {
            $this->arrErr = [];

            $this->tpl_mainpage = 'products/select_find2.tpl';

            return;
        }

        $this->doMobileSelectItem();
    }

    /**
     * @return void
     */
    public function doMobileSelectItem()
    {
        $objProduct = new SC_Product_Ex();

        $this->arrErr = $this->lfCheckError($this->mode, $this->objFormParam, $this->tpl_classcat_find1, $this->tpl_classcat_find2);

        // この段階では、商品規格ID・数量の入力チェックエラーを出させない。
        // FIXME: エラーチェックの定義で mode で定義を分岐する方が良いように感じる
        unset($this->arrErr['product_class_id']);
        unset($this->arrErr['quantity']);

        // 規格2が設定されていて、エラーを検出した場合
        if ($this->tpl_classcat_find2 && !empty($this->arrErr)) {
            // templateの変更
            $this->tpl_mainpage = 'products/select_find2.tpl';

            return;
        }

        $product_id = $this->objFormParam->getValue('product_id');

        $value1 = $this->objFormParam->getValue('classcategory_id1');
        if (strlen($value1) === 0) {
            $value1 = '__unselected';
        }

        // 規格2が設定されている場合.
        if (SC_Utils_Ex::isBlank($this->objFormParam->getValue('classcategory_id2')) == false) {
            $value2 = '#'.$this->objFormParam->getValue('classcategory_id2');
        } else {
            $value2 = '#0';
        }

        $objProduct->setProductsClassByProductIds([$product_id]);
        $this->tpl_product_class_id = $objProduct->classCategories[$product_id][$value1][$value2]['product_class_id'];

        // 数量の入力を行う
        $this->tpl_mainpage = 'products/select_item.tpl';
    }

    /**
     * @return void
     */
    public function doMobileCart()
    {
        // この段階でエラーが出る場合は、数量の入力エラーのはず
        if (count($this->arrErr)) {
            // 数量の入力を行う
            $this->tpl_mainpage = 'products/select_item.tpl';
        }
    }

    /**
     * @return void
     */
    public function doMobileDefault()
    {
        $this->tpl_mainpage = 'products/detail.tpl';
    }
}
