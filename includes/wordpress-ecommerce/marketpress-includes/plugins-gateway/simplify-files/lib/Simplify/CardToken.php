<?php
/*
 * Copyright (c) 2013, MasterCard International Incorporated
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, are 
 * permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of 
 * conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its 
 * contributors may be used to endorse or promote products derived from this software 
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES 
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT 
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER 
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING 
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF 
 * SUCH DAMAGE.
 */


class Simplify_CardToken extends Simplify_Object {
    /**
     * Creates an Simplify_CardToken object
     * @param     array $hash a map of parameters; valid keys are:<dl style="padding-left:10px;">
     *     <dt><tt>callback</tt></dt>    <dd>The URL callback for the cardtoken </dd>
     *     <dt><tt>card.addressCity</tt></dt>    <dd>City of the cardholder. </dd>
     *     <dt><tt>card.addressCountry</tt></dt>    <dd>Country code (ISO-3166-1-alpha-2 code) of residence of the cardholder. </dd>
     *     <dt><tt>card.addressLine1</tt></dt>    <dd>Address of the cardholder. </dd>
     *     <dt><tt>card.addressLine2</tt></dt>    <dd>Address of the cardholder if needed. </dd>
     *     <dt><tt>card.addressState</tt></dt>    <dd>State code (USPS code) of residence of the cardholder. </dd>
     *     <dt><tt>card.addressZip</tt></dt>    <dd>Postal code of the cardholder. </dd>
     *     <dt><tt>card.cvc</tt></dt>    <dd>CVC security code of the card. This is the code on the back of the card. Example: 123 </dd>
     *     <dt><tt>card.expMonth</tt></dt>    <dd>Expiration month of the card. Format is MM. Example: January = 01 <strong>required </strong></dd>
     *     <dt><tt>card.expYear</tt></dt>    <dd>Expiration year of the card. Format is YY. Example: 2013 = 13 <strong>required </strong></dd>
     *     <dt><tt>card.name</tt></dt>    <dd>Name as appears on the card. </dd>
     *     <dt><tt>card.number</tt></dt>    <dd>Card number as it appears on the card. <strong>required </strong></dd>
     *     <dt><tt>key</tt></dt>    <dd>Key used to create the card token. </dd></dl>
     * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
     * @param     string privateKey Private key. If null, the value of static Simplify::$privateKey will be used
     * @return    CardToken a CardToken object.
     */
    static public function createCardToken($hash, $publicKey = null, $privateKey = null) {

        $instance = new Simplify_CardToken();
        $instance->setAll($hash);

        $object = Simplify_PaymentsApi::createObject($instance, $publicKey, $privateKey);
        return $object;
    }



        /**
         * Retrieve a Simplify_CardToken object from the API
         *
         * @param     string id  the id of the CardToken object to retrieve
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    CardToken a CardToken object
         */
        static public function findCardToken($id, $publicKey = null, $privateKey = null) {
            $val = new Simplify_CardToken();
            $val->id = $id;

            $obj = Simplify_PaymentsApi::findObject($val, $publicKey, $privateKey);

            return $obj;
        }

    /**
     * @ignore
     */
    public function getClazz() {
        return "CardToken";
    }
}