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


class Simplify_Plan extends Simplify_Object {
    /**
     * Creates an Simplify_Plan object
     * @param     array $hash a map of parameters; valid keys are:<dl style="padding-left:10px;">
     *     <dt><tt>amount</tt></dt>    <dd>Amount of payment for the plan in minor units. Example: 1000 = 10.00 <strong>required </strong></dd>
     *     <dt><tt>currency</tt></dt>    <dd>Currency code (ISO-4217) for the plan. Must match the currency associated with your account. <strong>required </strong><strong>default:USD</strong></dd>
     *     <dt><tt>frequency</tt></dt>    <dd>Frequency of payment for the plan. Example: Monthly <strong>required </strong></dd>
     *     <dt><tt>name</tt></dt>    <dd>Name of the plan <strong>required </strong></dd></dl>
     * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
     * @param     string privateKey Private key. If null, the value of static Simplify::$privateKey will be used
     * @return    Plan a Plan object.
     */
    static public function createPlan($hash, $publicKey = null, $privateKey = null) {

        $instance = new Simplify_Plan();
        $instance->setAll($hash);

        $object = Simplify_PaymentsApi::createObject($instance, $publicKey, $privateKey);
        return $object;
    }




       /**
        * Deletes an Simplify_Plan object.
        *
        * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
        * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
        */
        public function deletePlan($publicKey = null, $privateKey = null) {
            $obj = Simplify_PaymentsApi::deleteObject($this, $publicKey, $privateKey);
            $this->properties = null;
            return true;
        }


       /**
        * Retrieve Simplify_Plan objects.
        * @param     array criteria a map of parameters; valid keys are:<dl style="padding-left:10px;">
        *     <dt><tt>filter</tt></dt>    <dd>Filters to apply to the list.  </dd>
        *     <dt><tt>max</tt></dt>    <dd>Allows up to a max of 50 list items to return.  <strong>default:20</strong></dd>
        *     <dt><tt>offset</tt></dt>    <dd>Used in paging of the list.  This is the start offset of the page.  <strong>default:0</strong></dd>
        *     <dt><tt>sorting</tt></dt>    <dd>Allows for ascending or descending sorting of the list.  The value maps properties to the sort direction (either <tt>asc</tt> for ascending or <tt>desc</tt> for descending).  Sortable properties are: <tt> dateCreated</tt><tt> amount</tt><tt> frequency</tt><tt> name</tt><tt> id</tt>.</dd></dl>
        * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
        * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
        * @return    ResourceList a ResourceList object that holds the list of Plan objects and the total
        *            number of Plan objects available for the given criteria.
        * @see       ResourceList
        */
        static public function listPlan($criteria = null, $publicKey = null, $privateKey = null) {
            $val = new Simplify_Plan();
            $list = Simplify_PaymentsApi::listObject($val, $criteria, $publicKey, $privateKey);

            return $list;
        }


        /**
         * Retrieve a Simplify_Plan object from the API
         *
         * @param     string id  the id of the Plan object to retrieve
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    Plan a Plan object
         */
        static public function findPlan($id, $publicKey = null, $privateKey = null) {
            $val = new Simplify_Plan();
            $val->id = $id;

            $obj = Simplify_PaymentsApi::findObject($val, $publicKey, $privateKey);

            return $obj;
        }


        /**
         * Updates an Simplify_Plan object.
         *
         * The properties that can be updated:
         * <ul>
         * 
         * 
         * <li>name <strong>(required)</strong></li>
         * </ul>
         * @param     string publicKey Public key. If null, the value of static Simplify::$publicKey will be used
         * @param     string privateKey Private key. If null, the value of Simplify::$privateKey will be used
         * @return    Plan a Plan object.
         */
        public function updatePlan($publicKey = null, $privateKey = null)  {
            $object = Simplify_PaymentsApi::updateObject($this, $publicKey, $privateKey);
            return $object;
        }

    /**
     * @ignore
     */
    public function getClazz() {
        return "Plan";
    }
}