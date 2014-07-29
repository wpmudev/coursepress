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


class Simplify_Coupon extends Simplify_Object {
    /**
     * Creates an Simplify_Coupon object
     * @param     array $hash a map of parameters; valid keys are:<dl style="padding-left:10px;">
     *     <dt><tt>amountOff</tt></dt>    <dd>Amount off of the price of the product in minor units in the currency of the merchant. While this field is optional, you must provide either amountOff or percentOff for a coupon. Example: 1000 = 10.00 </dd>
     *     <dt><tt>couponCode</tt></dt>    <dd>Code that identifies the coupon to be used. <strong>required </strong></dd>
     *     <dt><tt>description</tt></dt>    <dd>A brief section that describes the coupon. </dd>
     *     <dt><tt>durationInMonths</tt></dt>    <dd>Duration in months that the coupon will be applied after it has first been selected. </dd>
     *     <dt><tt>endDate</tt></dt>    <dd>Last date of the coupon in UTC millis that the coupon can be applied to a subscription. This ends at 23:59:59 of the merchant timezone. </dd>
     *     <dt><tt>maxRedemptions</tt></dt>    <dd>Maximum number of redemptions allowed for the coupon. A redemption is defined as when the coupon is applied to the subscription for the first time. </dd>
     *     <dt><tt>percentOff</tt></dt>    <dd>Percentage off of the price of the product. While this field is optional, you must provide either amountOff or percentOff for a coupon. The percent off is a whole number. </dd>
     *     <dt><tt>startDate</tt></dt>    <dd>First date of the coupon in UTC millis that the coupon can be applied to a subscription. This starts at midnight of the merchant timezone. <strong>required </strong></dd></dl>
     * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
     * @param     string privateKey Private key. If null, the value of static Simplify::$privateKey will be used
     * @return    Coupon a Coupon object.
     */
    static public function createCoupon($hash, $publicKey = null, $privateKey = null) {

        $instance = new Simplify_Coupon();
        $instance->setAll($hash);

        $object = Simplify_PaymentsApi::createObject($instance, $publicKey, $privateKey);
        return $object;
    }




       /**
        * Deletes an Simplify_Coupon object.
        *
        * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
        * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
        */
        public function deleteCoupon($publicKey = null, $privateKey = null) {
            $obj = Simplify_PaymentsApi::deleteObject($this, $publicKey, $privateKey);
            $this->properties = null;
            return true;
        }


       /**
        * Retrieve Simplify_Coupon objects.
        * @param     array criteria a map of parameters; valid keys are:<dl style="padding-left:10px;">
        *     <dt><tt>filter</tt></dt>    <dd>Filters to apply to the list.  </dd>
        *     <dt><tt>max</tt></dt>    <dd>Allows up to a max of 50 list items to return.  <strong>default:20</strong></dd>
        *     <dt><tt>offset</tt></dt>    <dd>Used in paging of the list.  This is the start offset of the page.  <strong>default:0</strong></dd>
        *     <dt><tt>sorting</tt></dt>    <dd>Allows for ascending or descending sorting of the list.  The value maps properties to the sort direction (either <tt>asc</tt> for ascending or <tt>desc</tt> for descending).  Sortable properties are: <tt> dateCreated</tt><tt> maxRedemptions</tt><tt> timesRedeemed</tt><tt> id</tt><tt> startDate</tt><tt> endDate</tt><tt> percentOff</tt><tt> couponCode</tt><tt> durationInMonths</tt><tt> amountOff</tt>.</dd></dl>
        * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
        * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
        * @return    ResourceList a ResourceList object that holds the list of Coupon objects and the total
        *            number of Coupon objects available for the given criteria.
        * @see       ResourceList
        */
        static public function listCoupon($criteria = null, $publicKey = null, $privateKey = null) {
            $val = new Simplify_Coupon();
            $list = Simplify_PaymentsApi::listObject($val, $criteria, $publicKey, $privateKey);

            return $list;
        }


        /**
         * Retrieve a Simplify_Coupon object from the API
         *
         * @param     string id  the id of the Coupon object to retrieve
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    Coupon a Coupon object
         */
        static public function findCoupon($id, $publicKey = null, $privateKey = null) {
            $val = new Simplify_Coupon();
            $val->id = $id;

            $obj = Simplify_PaymentsApi::findObject($val, $publicKey, $privateKey);

            return $obj;
        }


        /**
         * Updates an Simplify_Coupon object.
         *
         * The properties that can be updated:
         * <ul>
         * <li>endDate </li>
         * 
         * 
         * 
         * <li>maxRedemptions </li>
         * </ul>
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    Coupon a Coupon object.
         */
        public function updateCoupon($publicKey = null, $privateKey = null)  {
            $object = Simplify_PaymentsApi::updateObject($this, $publicKey, $privateKey);
            return $object;
        }

    /**
     * @ignore
     */
    public function getClazz() {
        return "Coupon";
    }
}