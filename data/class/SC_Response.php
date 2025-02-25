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
 * HttpResponse を扱うクラス.
 *
 * @author Ryuichi Tokugami
 *
 * @version $Id$
 */
class SC_Response
{
    /**
     * コンテンツタイプ
     * Enter description here ...
     *
     * @var unknown_type
     */
    public $contentType;
    public $body;
    public $statusCode;
    public $header = [];

    /**
     * Enter description here ...
     */
    public $encoding;

    /**
     * レスポンス出力を書き込む.
     */
    public function write()
    {
        $this->sendHeader();
        echo $this->body;
    }

    public function sendHeader()
    {
        if (headers_sent()) {
            return;
        }
        // HTTPのヘッダ
        foreach ($this->header as $name => $head) {
            header($name.': '.$head);
        }
        if (strlen($this->statusCode) >= 1) {
            $this->sendHttpStatus($this->statusCode);
        }
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->header['Content-Type'] = $contentType;
    }

    public function setResposeBody($body)
    {
        $this->body = $body;
    }

    public function addHeader($name, $value)
    {
        $this->header[$name] = $value;
    }

    public function containsHeader($name)
    {
        return isset($this->header[$name]);
    }

    /**
     * アプリケーションのexit処理をする。以降の出力は基本的に停止する。
     * 各クラス内では、exit を直接呼び出さない。
     */
    public static function actionExit()
    {
        // ローカルフックポイント処理
        $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();

        if (is_object($objPlugin)) {
            $arrBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            // 0 番目には object が含まれない場合がある
            foreach ($arrBacktrace as $backtrace) {
                if (array_key_exists('object', $backtrace)
                    && is_object($backtrace['object'])) {
                    $parent_class_name = get_parent_class($backtrace['object']);
                    $objPlugin->doAction($parent_class_name.'_action_'.$backtrace['object']->getMode(), [$backtrace['object']]);
                    $class_name = get_class($backtrace['object']);
                    if ($class_name != $parent_class_name) {
                        $objPlugin->doAction($class_name.'_action_'.$backtrace['object']->getMode(), [$backtrace['object']]);
                    }
                    break;
                }
            }
        }

        static::exitWrapper();
        // デストラクタが実行される。
    }

    /**
     * アプリケーション内でリダイレクトする
     *
     * 内部で生成する URL のクエリは、下記の順で上書きしていく。(後勝ち)
     * 1. 引数 $inheritQueryString が true の場合、$_SERVER['QUERY_STRING']
     * 2. $location に含まれる クエリ
     * 3. 引数 $arrQueryString
     *
     * @param  string    $location           「url-path」「現在のURLからのパス」「URL」のいずれか。「../」の解釈は行なわない。
     * @param  array     $arrQueryString     URL に付加するクエリ
     * @param  bool      $inheritQueryString 現在のリクエストのクエリを継承するか
     * @param  bool|null $useSsl             true:HTTPSを強制, false:HTTPを強制, null:継承
     *
     * @return void
     *
     * @static
     */
    public static function sendRedirect($location, $arrQueryString = [], $inheritQueryString = false, $useSsl = null)
    {
        // ローカルフックポイント処理
        $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();

        if (is_object($objPlugin)) {
            $arrBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            // 0 番目には object が含まれない場合がある
            foreach ($arrBacktrace as $backtrace) {
                if (array_key_exists('object', $backtrace) && is_object($backtrace['object']) && method_exists($backtrace['object'], 'getMode')) {
                    $parent_class_name = get_parent_class($backtrace['object']);
                    $objPlugin->doAction($parent_class_name.'_action_'.$backtrace['object']->getMode(), [$backtrace['object']]);

                    $class_name = get_class($backtrace['object']);
                    if ($class_name != $parent_class_name) {
                        $objPlugin->doAction($class_name.'_action_'.$backtrace['object']->getMode(), [$backtrace['object']]);
                    }

                    break;
                } elseif (array_key_exists('object', $backtrace) && is_object($backtrace['object'])) {
                    $pattern = '/^[a-zA-Z0-9_]+$/';
                    $mode = null;
                    if (isset($_GET['mode']) && preg_match($pattern, $_GET['mode'])) {
                        $mode = $_GET['mode'];
                    } elseif (isset($_POST['mode']) && preg_match($pattern, $_POST['mode'])) {
                        $mode = $_POST['mode'];
                    }
                    $parent_class_name = get_parent_class($backtrace['object']);
                    $objPlugin->doAction($parent_class_name.'_action_'.$mode, [$backtrace['object']]);

                    $class_name = get_class($backtrace['object']);
                    if ($class_name != $parent_class_name) {
                        $objPlugin->doAction($class_name.'_action_'.$mode, [$backtrace['object']]);
                    }

                    break;
                }
            }
        }

        // url-path → URL 変換
        if ($location !== '' && $location[0] === '/') {
            $netUrl = new Net_URL($location);
            $location = $netUrl->getUrl();
        }

        // URL の場合
        if (preg_match('/^https?:/', $location)) {
            $url = $location;
            if (is_bool($useSsl)) {
                if ($useSsl) {
                    $pattern = '/^'.preg_quote(HTTP_URL, '/').'(.*)/';
                    $replacement = HTTPS_URL.'\1';
                    $url = preg_replace($pattern, $replacement, $url);
                } else {
                    $pattern = '/^'.preg_quote(HTTPS_URL, '/').'(.*)/';
                    $replacement = HTTP_URL.'\1';
                    $url = preg_replace($pattern, $replacement, $url);
                }
            }
        // 現在のURLからのパス
        } else {
            if (!is_bool($useSsl)) {
                $useSsl = SC_Utils_Ex::sfIsHTTPS();
            }
            $netUrl = new Net_URL($useSsl ? HTTPS_URL : HTTP_URL);
            $netUrl->path = dirname($_SERVER['SCRIPT_NAME']).'/'.$location;
            $url = $netUrl->getUrl();
        }

        if (!SC_Utils_Ex::isInternalUrl($url)) {
            trigger_error('アプリケーション外へのリダイレクトは扱わない: '.var_export($url, true), E_USER_ERROR);
        }

        $netUrl = new Net_URL($url);

        if ($inheritQueryString && !empty($_SERVER['QUERY_STRING'])) {
            $arrQueryStringBackup = $netUrl->querystring;
            // XXX メソッド名は add で始まるが、実際には置換を行う
            $netUrl->addRawQueryString($_SERVER['QUERY_STRING']);
            $netUrl->querystring = array_merge($netUrl->querystring, $arrQueryStringBackup);
        }

        foreach ($arrQueryString as $name => $value) {
            $netUrl->addQueryString($name, $value);
        }

        $session = SC_SessionFactory_Ex::getInstance();
        if ((SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE)
            || ($session->useCookie() == false)
        ) {
            $netUrl->addQueryString(session_name(), session_id());
        }

        /*
         * transactionid を受け取ったリクエストに関して、値を継承してリダイレクトする。
         * @see https://github.com/EC-CUBE/ec-cube2/issues/922
         */
        if (// 管理機能 (本来遷移先で判定すべきだが、簡易的に遷移元で判定している。)
            GC_Utils_Ex::isAdminFunction()
            // 遷移元 transactionid 指定あり
            && isset($_REQUEST[TRANSACTION_ID_NAME])
            // リダイレクト先 mode 指定あり
            && isset($netUrl->querystring['mode'])
            // リダイレクト先 transactionid 指定なし
            && !isset($netUrl->querystring[TRANSACTION_ID_NAME])
        ) {
            $netUrl->addQueryString(TRANSACTION_ID_NAME, $_REQUEST[TRANSACTION_ID_NAME]);
        }

        $url = $netUrl->getURL();

        if (!headers_sent()) {
            header("Location: $url");
        }
        static::exitWrapper();
    }

    /**
     * /html/ からのパスを指定してリダイレクトする
     *
     * FIXME メソッド名を分かりやすくしたい。現状だと、引数が「url-path より後」とも「url-path」とも読み取れる。(前者が意図したいところ)
     *
     * @param  string $location /html/ からのパス。先頭に / を含むかは任意。「../」の解釈は行なわない。
     *
     * @return void
     *
     * @static
     */
    public static function sendRedirectFromUrlPath($location, $arrQueryString = [], $inheritQueryString = false, $useSsl = null)
    {
        $location = ROOT_URLPATH.ltrim($location, '/');
        SC_Response_Ex::sendRedirect($location, $arrQueryString, $inheritQueryString, $useSsl);
    }

    /**
     * @static
     */
    public static function reload($arrQueryString = [], $removeQueryString = false)
    {
        // 現在の URL を取得
        $netUrl = new Net_URL($_SERVER['REQUEST_URI']);

        if ($removeQueryString) {
            $netUrl->querystring = [];
        }

        SC_Response_Ex::sendRedirect($netUrl->getURL(), $arrQueryString);
    }

    public function setHeader($headers)
    {
        $this->header = $headers;
    }

    public function setStatusCode($statusCode = null)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * HTTPステータスコードを送出する。
     *
     * @param  int $statusCode HTTPステータスコード
     *
     * @return void
     *
     * @author Seasoft (新規作成)
     *
     * @see Moony_Action::status() (オリジナル)
     * @see http://moony.googlecode.com/ (オリジナル)
     *
     * @author YAMAOKA Hiroyuki (オリジナル)
     * @copyright 2005-2008 YAMAOKA Hiroyuki (オリジナル)
     * @license http://opensource.org/licenses/bsd-license.php New BSD License (オリジナル)
     *
     * @see http://ja.wikipedia.org/wiki/HTTP%E3%82%B9%E3%83%86%E3%83%BC%E3%82%BF%E3%82%B9%E3%82%B3%E3%83%BC%E3%83%89 (邦訳)
     *
     * @license http://www.gnu.org/licenses/fdl.html GFDL (邦訳)
     *
     * @static
     */
    public static function sendHttpStatus($statusCode)
    {
        $protocol = $_SERVER['SERVER_PROTOCOL'];
        $httpVersion = (str_contains($protocol, '1.1')) ? '1.1' : '1.0';
        $messages = [
            // Informational 1xx                        // 【情報】
            100 => 'Continue',                          // 継続
            101 => 'Switching Protocols',               // プロトコル切替え
            // Success 2xx                              // 【成功】
            200 => 'OK',                                // OK
            201 => 'Created',                           // 作成
            202 => 'Accepted',                          // 受理
            203 => 'Non-Authoritative Information',     // 信頼できない情報
            204 => 'No Content',                        // 内容なし
            205 => 'Reset Content',                     // 内容のリセット
            206 => 'Partial Content',                   // 部分的内容
            // Redirection 3xx                          // 【リダイレクション】
            300 => 'Multiple Choices',                  // 複数の選択
            301 => 'Moved Permanently',                 // 恒久的に移動した
            302 => 'Found',  // 1.1                     // 発見した (リクエストしたリソースは一時的に移動されているときに返される)
            303 => 'See Other',                         // 他を参照せよ
            304 => 'Not Modified',                      // 未更新
            305 => 'Use Proxy',                         // プロキシを使用せよ
            // 306 is no longer used but still reserved // 将来のために予約されている
            307 => 'Temporary Redirect',                // 一時的リダイレクト
            // Client Error 4xx                         // 【クライアントエラー】
            400 => 'Bad Request',                       // リクエストが不正である
            401 => 'Unauthorized',                      // 認証が必要である
            402 => 'Payment Required',                  // 支払いが必要である
            403 => 'Forbidden',                         // 禁止されている
            404 => 'Not Found',                         // 未検出
            405 => 'Method Not Allowed',                // 許可されていないメソッド
            406 => 'Not Acceptable',                    // 受理できない
            407 => 'Proxy Authentication Required',     // プロキシ認証が必要である
            408 => 'Request Timeout',                   // リクエストタイムアウト
            409 => 'Conflict',                          // 矛盾
            410 => 'Gone',                              // 消滅した
            411 => 'Length Required',                   // 長さが必要
            412 => 'Precondition Failed',               // 前提条件で失敗した
            413 => 'Request Entity Too Large',          // リクエストエンティティが大きすぎる
            414 => 'Request-URI Too Long',              // リクエストURIが大きすぎる
            415 => 'Unsupported Media Type',            // サポートしていないメディアタイプ
            416 => 'Requested Range Not Satisfiable',   // リクエストしたレンジは範囲外にある
            417 => 'Expectation Failed',                // 期待するヘッダに失敗
            // Server Error 5xx                         // 【サーバーエラー】
            500 => 'Internal Server Error',             // サーバー内部エラー
            501 => 'Not Implemented',                   // 実装されていない
            502 => 'Bad Gateway',                       // 不正なゲートウェイ
            503 => 'Service Unavailable',               // サービス利用不可
            504 => 'Gateway Timeout',                   // ゲートウェイタイムアウト
            505 => 'HTTP Version Not Supported',        // サポートしていないHTTPバージョン
            509 => 'Bandwidth Limit Exceeded',           // 帯域幅制限超過
        ];
        if (isset($messages[$statusCode])) {
            if ($httpVersion !== '1.1') {
                // HTTP/1.0
                $messages[302] = 'Moved Temporarily';
            }
            header("HTTP/{$httpVersion} {$statusCode} {$messages[$statusCode]}");
            header("Status: {$statusCode} {$messages[$statusCode]}", true, $statusCode);
        }
    }

    /**
     * ダウンロード用の HTTP ヘッダを出力する
     *
     * @param string $file_name
     *
     * @return void
     */
    public static function headerForDownload($file_name)
    {
        if (!headers_sent()) {
            header("Content-disposition: attachment; filename={$file_name}");
            header("Content-type: application/octet-stream; name={$file_name}");
            header('Cache-Control: ');
            header('Pragma: ');
        }
    }

    /**
     * exit をスキップする場合はオーバーライドすること.
     */
    protected static function exitWrapper()
    {
        exit;
    }
}
