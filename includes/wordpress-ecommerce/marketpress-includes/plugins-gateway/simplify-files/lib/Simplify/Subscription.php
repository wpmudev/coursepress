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


class Simplify_Subscription extends Simplify_Object {
    /**
     * Creates an Simplify_Subscription object
     * @param     array $hash a map of parameters; valid keys are:<dl style="padding-left:10px;">
     *     <dt><tt>amount</tt></dt>    <dd>Amount of the payment (minor units). Example: 1000 = 10.00 </dd>
     *     <dt><tt>coupon</tt></dt>    <dd>Coupon ID associated with the subscription </dd>
     *     <dt><tt>currency</tt></dt>    <dd>Currency code (ISO-4217). Must match the currency associated with your account. <strong>default:USD</strong></dd>
     *     <dt><tt>customer</tt></dt>    <dd>Customer that is enrolling in the subscription. </dd>
     *     <dt><tt>frequency</tt></dt>    <dd>Frequency of payment for the plan. Example: Monthly </dd>
     *     <dt><tt>name</tt></dt>    <dd>Name describing subscription </dd>
     *     <dt><tt>plan</tt></dt>    <dd>The ID of the plan that should be used for the subscription. </dd>
     *     <dt><tt>quantity</tt></dt>    <dd>Quantity of the plan for the subscription. </dd></dl>
     * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
     * @param     string privateKey Private key. If null, the value of static Simplify::$privateKey will be used
     * @return    Subscription a Subscription object.
     */
    static public function createSubscription($hash, $publicKey = null, $privateKey = null) {

        $instance = new Simplify_Subscription();
        $instance->setAll($hash);

        $object = Simplify_PaymentsApi::createObject($instance, $publicKey, $privateKey);
        return $object;
    }




       /**
        * Deletes an Simplify_Subscription object.
        *
        * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
        * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
        */
        public function deleteSubscription($publicKey = null, $privateKey = null) {
            $obj = Simplify_PaymentsApi::deleteObject($this, $publicKey, $privateKey);
            $this->properties = null;
            return true;
        }


       /**
        * Retrieve Simplify_Subscription objects.
        * @param     array criteria a map of parameters; valid keys are:<dl style="padding-left:10px;">
        *     <dt><tt>filter</tt></dt>    <dd>Filters to apply to the list.  </dd>
        *     <dt><tt>max</tt></dt>    <dd>Allows up to a max of 50 list items to return.  <strong>default:20</strong></dd>
        *     <dt><tt>offset</tt></dt>    <dd>Used in paging of the list.  This is the start offset of the page.  <strong>default:0</strong></dd>
        *     <dt><tt>sorting</tt></dt>    <dd>Allows for ascending or descending sorting of the list.  The value maps properties to the sort direction (either <tt>asc</tt> for ascending or <tt>desc</tt> for descending).  Sortable properties are: <tt> id</tt><tt> plan</tt>.</dd></dl>
        * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
        * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
        * @return    ResourceList a ResourceList object that holds the list of Subscription objects and the total
        *            number of Subscription objects available for the given criteria.
        * @see       ResourceList
        */
        static public function listSubscription($criteria = null, $publicKey = null, $privateKey = null) {
            $val = new Simplify_Subscription();
            $list = Simplify_PaymentsApi::listObject($val, $criteria, $publicKey, $privateKey);

            return $list;
        }


        /**
         * Retrieve a Simplify_Subscription object from the API
         *
         * @param     string id  the id of the Subscription object to retrieve
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    Subscription a Subscription object
         */
        static public function findSubscription($id, $publicKey = null, $privateKey = null) {
            $val = new Simplify_Subscription();
            $val->id = $id;

            $obj = Simplify_PaymentsApi::findObject($val, $publicKey, $privateKey);

            return $obj;
        }


        /**
         * Updates an Simplify_Subscription object.
         *
         * The properties that can be updated:
         * <ul>
         * <li>amount </li>
         * 
         * <li>coupon </li>
         * 
         * <li>currency </li>
         * 
         * <li>frequency </li>
         * 
         * 
         * 
         * <li>name </li>
         * 
         * <li>plan </li>
         * 
         * <li>prorate <strong>(required)</strong></li>
         * 
         * <li>quantity </li>
         * </ul>
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    Subscription a Subscription object.
         */
        public function updateSubscription($publicKey = null, $privateKey = null)  {
            $object = Simplify_PaymentsApi::updateObject($this, $publicKey, $privateKey);
            return $object;
        }

    /**
     * @ignore
     */
    public function getClazz() {
        return "Subscription";
    }
}