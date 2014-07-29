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


class Simplify_Customer extends Simplify_Object {
    /**
     * Creates an Simplify_Customer object
     * @param     array $hash a map of parameters; valid keys are:<dl style="padding-left:10px;">
     *     <dt><tt>card.addressCity</tt></dt>    <dd>City of the cardholder. </dd>
     *     <dt><tt>card.addressCountry</tt></dt>    <dd>Country code (ISO-3166-1-alpha-2 code) of residence of the cardholder. </dd>
     *     <dt><tt>card.addressLine1</tt></dt>    <dd>Address of the cardholder </dd>
     *     <dt><tt>card.addressLine2</tt></dt>    <dd>Address of the cardholder if needed. </dd>
     *     <dt><tt>card.addressState</tt></dt>    <dd>State code (USPS code) of residence of the cardholder. </dd>
     *     <dt><tt>card.addressZip</tt></dt>    <dd>Postal code of the cardholder. </dd>
     *     <dt><tt>card.cvc</tt></dt>    <dd>CVC security code of the card. This is the code on the back of the card. Example: 123 </dd>
     *     <dt><tt>card.expMonth</tt></dt>    <dd>Expiration month of the card. Format is MM. Example: January = 01 <strong>required </strong></dd>
     *     <dt><tt>card.expYear</tt></dt>    <dd>Expiration year of the card. Format is YY. Example: 2013 = 13 <strong>required </strong></dd>
     *     <dt><tt>card.name</tt></dt>    <dd>Name as appears on the card. </dd>
     *     <dt><tt>card.number</tt></dt>    <dd>Card number as it appears on the card. <strong>required </strong></dd>
     *     <dt><tt>email</tt></dt>    <dd>Email address of the customer <strong>required </strong></dd>
     *     <dt><tt>name</tt></dt>    <dd>Customer name <strong>required </strong></dd>
     *     <dt><tt>reference</tt></dt>    <dd>Reference field for external applications use. </dd>
     *     <dt><tt>subscriptions.amount</tt></dt>    <dd>Amount of payment in minor units. Example: 1000 = 10.00 </dd>
     *     <dt><tt>subscriptions.coupon</tt></dt>    <dd>Coupon associated with the subscription for the customer. </dd>
     *     <dt><tt>subscriptions.currency</tt></dt>    <dd>Currency code (ISO-4217). Must match the currency associated with your account. <strong>default:USD</strong></dd>
     *     <dt><tt>subscriptions.customer</tt></dt>    <dd>The customer ID to create the subscription for. Do not supply this when creating a customer. </dd>
     *     <dt><tt>subscriptions.frequency</tt></dt>    <dd>Frequency of payment for the plan. Example: Monthly </dd>
     *     <dt><tt>subscriptions.name</tt></dt>    <dd>Name describing subscription </dd>
     *     <dt><tt>subscriptions.plan</tt></dt>    <dd>The plan ID that the subscription should be created from. </dd>
     *     <dt><tt>subscriptions.quantity</tt></dt>    <dd>Quantity of the plan for the subscription. </dd></dl>
     * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
     * @param     string privateKey Private key. If null, the value of static Simplify::$privateKey will be used
     * @return    Customer a Customer object.
     */
    static public function createCustomer($hash, $publicKey = null, $privateKey = null) {

        $instance = new Simplify_Customer();
        $instance->setAll($hash);

        $object = Simplify_PaymentsApi::createObject($instance, $publicKey, $privateKey);
        return $object;
    }




       /**
        * Deletes an Simplify_Customer object.
        *
        * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
        * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
        */
        public function deleteCustomer($publicKey = null, $privateKey = null) {
            $obj = Simplify_PaymentsApi::deleteObject($this, $publicKey, $privateKey);
            $this->properties = null;
            return true;
        }


       /**
        * Retrieve Simplify_Customer objects.
        * @param     array criteria a map of parameters; valid keys are:<dl style="padding-left:10px;">
        *     <dt><tt>filter</tt></dt>    <dd>Filters to apply to the list.  </dd>
        *     <dt><tt>max</tt></dt>    <dd>Allows up to a max of 50 list items to return.  <strong>default:20</strong></dd>
        *     <dt><tt>offset</tt></dt>    <dd>Used in paging of the list.  This is the start offset of the page.  <strong>default:0</strong></dd>
        *     <dt><tt>sorting</tt></dt>    <dd>Allows for ascending or descending sorting of the list.  The value maps properties to the sort direction (either <tt>asc</tt> for ascending or <tt>desc</tt> for descending).  Sortable properties are: <tt> dateCreated</tt><tt> id</tt><tt> name</tt><tt> email</tt><tt> reference</tt>.</dd></dl>
        * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
        * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
        * @return    ResourceList a ResourceList object that holds the list of Customer objects and the total
        *            number of Customer objects available for the given criteria.
        * @see       ResourceList
        */
        static public function listCustomer($criteria = null, $publicKey = null, $privateKey = null) {
            $val = new Simplify_Customer();
            $list = Simplify_PaymentsApi::listObject($val, $criteria, $publicKey, $privateKey);

            return $list;
        }


        /**
         * Retrieve a Simplify_Customer object from the API
         *
         * @param     string id  the id of the Customer object to retrieve
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    Customer a Customer object
         */
        static public function findCustomer($id, $publicKey = null, $privateKey = null) {
            $val = new Simplify_Customer();
            $val->id = $id;

            $obj = Simplify_PaymentsApi::findObject($val, $publicKey, $privateKey);

            return $obj;
        }


        /**
         * Updates an Simplify_Customer object.
         *
         * The properties that can be updated:
         * <ul>
         * <li>card.addressCity </li>
         * 
         * <li>card.addressCountry </li>
         * 
         * <li>card.addressLine1 </li>
         * 
         * <li>card.addressLine2 </li>
         * 
         * <li>card.addressState </li>
         * 
         * <li>card.addressZip </li>
         * 
         * <li>card.cvc </li>
         * 
         * <li>card.expMonth <strong>(required)</strong></li>
         * 
         * <li>card.expYear <strong>(required)</strong></li>
         * 
         * <li>card.name </li>
         * 
         * <li>card.number <strong>(required)</strong></li>
         * 
         * <li>email <strong>(required)</strong></li>
         * 
         * 
         * 
         * <li>name <strong>(required)</strong></li>
         * 
         * <li>reference </li>
         * </ul>
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    Customer a Customer object.
         */
        public function updateCustomer($publicKey = null, $privateKey = null)  {
            $object = Simplify_PaymentsApi::updateObject($this, $publicKey, $privateKey);
            return $object;
        }

    /**
     * @ignore
     */
    public function getClazz() {
        return "Customer";
    }
}