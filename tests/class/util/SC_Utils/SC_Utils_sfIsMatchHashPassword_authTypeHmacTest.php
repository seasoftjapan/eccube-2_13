<?php

$HOME = realpath(__DIR__).'/../../../..';
// このテスト専用の定数の設定
defined('AUTH_TYPE') || define('AUTH_TYPE', 'HMAC');
require_once $HOME.'/tests/class/Common_TestCase.php';
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
 * SC_Utils::sfIsMatchHashPassword()のテストクラス.
 * 暗号化の詳細については特にテストしていない.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Utils_sfIsMatchHashPassword_authTypeHmacTest extends Common_TestCase
{
    protected function setUp(): void
    {
        // parent::setUp();
    }

    protected function tearDown(): void
    {
        // parent::tearDown();
    }

    // ///////////////////////////////////////
    public function testSfIsMatchHashPasswordハッシュ化後の文字列が一致する場合Trueが返る()
    {
        $pass = 'ec-cube';
        $salt = 'salt';
        $hashpass = SC_Utils_Ex::sfGetHashString($pass, $salt);

        $this->expected = true;
        $this->actual = SC_Utils::sfIsMatchHashPassword($pass, $hashpass, $salt);

        $this->verify('パスワード文字列比較結果');
    }

    public function testSfIsMatchHashPasswordハッシュ化後の文字列が一致しない場合Falseが返る()
    {
        $pass = 'ec-cube';
        $salt = 'salt';
        $hashpass = 'ec-cube';

        $this->expected = false;
        $this->actual = SC_Utils::sfIsMatchHashPassword($pass, $hashpass, $salt);

        $this->verify('パスワード文字列比較結果');
    }

    public function testSfIsMatchHashPasswordSaltが未指定の場合旧バージョンの暗号化で比較される()
    {
        $pass = 'ec-cube';
        $hashpass = sha1($pass.':'.AUTH_MAGIC);
        $salt = '';

        $this->expected = true;
        $this->actual = SC_Utils::sfIsMatchHashPassword($pass, $hashpass, $salt);

        $this->verify('パスワード文字列比較結果');
    }
}
