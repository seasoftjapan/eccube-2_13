<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Purchase/SC_Helper_Purchase_TestBase.php';
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
 * SC_Helper_Purchase::copyFromCustomer()のテストクラス.
 *
 * @author Hiroko Tamagawa
 *
 * @version $Id$
 */
class SC_Helper_Purchase_copyFromCustomerTest extends SC_Helper_Purchase_TestBase
{
    public $customer;
    public $customer_array;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = new SC_Customer_Ex();
        $this->customer->setValue('customer_id', '1001');
        $this->customer->setValue('name01', '姓01');
        $this->customer->setValue('name02', '名01');
        $this->customer->setValue('kana01', 'セイ01');
        $this->customer->setValue('kana02', 'メイ01');
        $this->customer->setValue('sex', '1');
        $this->customer->setValue('zip01', '123');
        $this->customer->setValue('zip02', '4567');
        $this->customer->setValue('pref', '東京都');
        $this->customer->setValue('addr01', 'abc市');
        $this->customer->setValue('addr02', 'def町');
        $this->customer->setValue('tel01', '01');
        $this->customer->setValue('tel02', '234');
        $this->customer->setValue('tel03', '5678');
        $this->customer->setValue('fax01', '02');
        $this->customer->setValue('fax02', '345');
        $this->customer->setValue('fax03', '6789');
        $this->customer->setValue('job', '会社員');
        $this->customer->setValue('birth', '2012-01-01');
        $this->customer->setValue('email', 'test@example.com');

        $this->customer_array = ['customer_id' => '1001', 'email' => 'test@example.com'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////
    public function testCopyFromCustomerログインしていない場合何もしない()
    {
        $dest = [];
        User_Utils::setLoginState(false, $this->customer_array, $this->objQuery);

        $this->expected = [];
        $helper = new SC_Helper_Purchase_Ex();
        $helper->copyFromCustomer($dest, $this->customer);
        $this->actual = $dest;

        $this->verify();
    }

    public function testCopyFromCustomerモバイルの場合モバイルのメールアドレスを設定する()
    {
        $this->markTestIncomplete('DEVICE_TYPE の切り替えテストは実装されていません');
        $dest = [];
        User_Utils::setLoginState(true, $this->customer_array, $this->objQuery);
        User_Utils::setDeviceType(DEVICE_TYPE_MOBILE);
        $this->customer->setValue('email_mobile', 'mobile@example.com');

        $this->expected = [
            'order_name01' => '姓01',
            'order_name02' => '名01',
            'order_kana01' => 'セイ01',
            'order_kana02' => 'メイ01',
            'order_sex' => '1',
            'order_zip01' => '123',
            'order_zip02' => '4567',
            'order_pref' => '東京都',
            'order_addr01' => 'abc市',
            'order_addr02' => 'def町',
            'order_tel01' => '01',
            'order_tel02' => '234',
            'order_tel03' => '5678',
            'order_fax01' => '02',
            'order_fax02' => '345',
            'order_fax03' => '6789',
            'order_job' => '会社員',
            'order_birth' => '2012-01-01',
            'order_email' => 'mobile@example.com',
            'customer_id' => '1001',
            'update_date' => 'CURRENT_TIMESTAMP',
            'order_country_id' => '',
            'order_company_name' => '',
            'order_zipcode' => '',
        ];
        $helper = new SC_Helper_Purchase_Ex();
        $helper->copyFromCustomer($dest, $this->customer);
        $this->actual = $dest;

        $this->verify();
    }

    public function testCopyFromCustomerモバイルかつモバイルのメールアドレスがない場合通常のメールアドレスを設定する()
    {
        $dest = [];
        $prefix = 'order';
        // キーを絞る
        $keys = ['name01', 'email'];
        User_Utils::setLoginState(true, $this->customer_array, $this->objQuery);
        User_Utils::setDeviceType(DEVICE_TYPE_MOBILE);

        $this->expected = [
            'order_name01' => '姓01',
            'order_email' => 'test@example.com',
            'customer_id' => '1001',
            'update_date' => 'CURRENT_TIMESTAMP',
        ];
        $helper = new SC_Helper_Purchase_Ex();
        $helper->copyFromCustomer($dest, $this->customer, $prefix, $keys);
        $this->actual = $dest;

        $this->verify();
    }

    public function testCopyFromCustomerモバイルでない場合通常のメールアドレスをそのまま設定する()
    {
        $dest = [];
        $prefix = 'prefix';
        // キーを絞る
        $keys = ['name01', 'email'];
        User_Utils::setLoginState(true, $this->customer_array, $this->objQuery);
        User_Utils::setDeviceType(DEVICE_TYPE_PC);
        $this->customer->setValue('email_mobile', 'mobile@example.com');

        $this->expected = [
            'prefix_name01' => '姓01',
            'prefix_email' => 'test@example.com',
            'customer_id' => '1001',
            'update_date' => 'CURRENT_TIMESTAMP',
        ];
        $helper = new SC_Helper_Purchase_Ex();
        $helper->copyFromCustomer($dest, $this->customer, $prefix, $keys);
        $this->actual = $dest;

        $this->verify();
    }

    // ////////////////////////////////////////
}
