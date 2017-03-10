<?php
/*
    part-db version 0.1
    Copyright (C) 2005 Christoph Lechner
    http://www.cl-projects.de/

    part-db version 0.2+
    Copyright (C) 2009 K. Jacobs and others (see authors.php)
    http://code.google.com/p/part-db/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

    /**
     * @file class.Company.php
     * @brief class Company
     *
     * @class Company
     * @brief This abstract class is used for companies like suppliers or manufacturers.
     * @author kami89
     */
    abstract class Company extends PartsContainingDBElement
    {
        /********************************************************************************
        *
        *   Constructor / Destructor / reset_attributes()
        *
        *********************************************************************************/

        /**
         * @brief Constructor
         *
         * @note  It's allowed to create an object with the ID 0 (for the root element).
         *
         * @param Database  &$database      reference to the Database-object
         * @param User      &$current_user  reference to the current user which is logged in
         * @param Log       &$log           reference to the Log-object
         * @param string    $tablename      the name of the database table (e.g. "suppliers" or "manufacturers")
         * @param integer   $id             ID of the database record we want to get
         *
         * @throws Exception        if there is no such element in the database
         * @throws Exception        if there was an error
         */
        public function __construct(&$database, &$current_user, &$log, $tablename, $id)
        {
            parent::__construct($database, $current_user, $log, $tablename, $id);

            if ($id == 0)
            {
                // this is the root node
                $this->db_data['address'] = '';
                $this->db_data['phone_number'] = '';
                $this->db_data['fax_number'] = '';
                $this->db_data['email_address'] = '';
                $this->db_data['website'] = '';
                return;
            }
        }

        /**
         * Builds a serializable array containing the infos about this Company
         * @param mixed $verbose Show verbose informations like address or phone_number about the company
         * @return array The array containing the Company informations
         */
        public function get_json_array($verbose = false)
        {
            if($verbose)
            {
                $ret = array('id' => $this->get_id(),
                             'name' => $this->get_name(),
                             'address' => $this->get_address(),
                             'phone_number' => $this->get_phone_number(),
                             'fax_number'  => $this->get_fax_number(),
                             'email_address' => $this->get_email_address(),
                             'website' => $this->get_website(),
                             'auto_url' => $this->get_auto_product_url);
            }
            else
            {
                $ret = array('id' => $this->get_id(),
                             'name' => $this->get_name(),
                             'auto_url' => $this->get_auto_product_url);
            }

            return $ret;
        }

        /********************************************************************************
        *
        *   Getters
        *
        *********************************************************************************/

        /**
         * @brief Get the address
         *
         * @retval string       the address of the company (with "\n" as line break)
         */
        public function get_address()
        {
            return $this->db_data['address'];
        }

        /**
         * @brief Get the phone number
         *
         * @retval string       the phone number of the company
         */
        public function get_phone_number()
        {
            return $this->db_data['phone_number'];
        }

        /**
         * @brief Get the fax number
         *
         * @retval string       the fax number of the company
         */
        public function get_fax_number()
        {
            return $this->db_data['fax_number'];
        }

        /**
         * @brief Get the e-mail address
         *
         * @retval string       the e-mail address of the company
         */
        public function get_email_address()
        {
            return $this->db_data['email_address'];
        }

        /**
         * @brief Get the website
         *
         * @retval string       the website of the company
         */
        public function get_website()
        {
            return $this->db_data['website'];
        }

        /**
         * @brief Get the link to the website of an article
         *
         * @param string $partnr    @li NULL for returning the URL with a placeholder for the part number
         *                          @li or the part number for returning the direct URL to the article
         *
         * @retval string           the link to the article
         */
        public function get_auto_product_url($partnr = NULL)
        {
            if (is_string($partnr))
                return str_replace('%PARTNUMBER%', $partnr, $this->db_data['auto_product_url']);
            else
                return $this->db_data['auto_product_url'];
        }

        /********************************************************************************
        *
        *   Setters
        *
        *********************************************************************************/

        /**
         * @brief Set the address
         *
         * @param string $new_address       the new address (with "\n" as line break)
         *
         * @throws Exception if there was an error
         */
        public function set_address($new_address)
        {
            $this->set_attributes(array('address' => $new_address));
        }

        /**
         * @brief Set the phone number
         *
         * @param string $new_phone_number       the new phone number
         *
         * @throws Exception if there was an error
         */
        public function set_phone_number($new_phone_number)
        {
            $this->set_attributes(array('phone_number' => $new_phone_number));
        }

        /**
         * @brief Set the fax number
         *
         * @param string $new_fax_number       the new fax number
         *
         * @throws Exception if there was an error
         */
        public function set_fax_number($new_fax_number)
        {
            $this->set_attributes(array('fax_number' => $new_fax_number));
        }

        /**
         * @brief Set the e-mail address
         *
         * @param string $new_email_address       the new e-mail address
         *
         * @throws Exception if there was an error
         */
        public function set_email_address($new_email_address)
        {
            $this->set_attributes(array('email_address' => $new_email_address));
        }

        /**
         * @brief Set the website
         *
         * @param string $new_website       the new website
         *
         * @throws Exception if there was an error
         */
        public function set_website($new_website)
        {
            $this->set_attributes(array('website' => $new_website));
        }

        /**
         * @brief Set the link to the website of an article
         *
         * @param string $new_url       the new URL with the placeholder %PARTNUMBER% for the part number
         *
         * @throws Exception if there was an error
         */
        public function set_auto_product_url($new_url)
        {
            $this->set_attributes(array('auto_product_url' => $new_url));
        }

        /********************************************************************************
        *
        *   Static Methods
        *
        *********************************************************************************/

        /**
         * @copydoc DBElement::check_values_validity()
         */
        public static function check_values_validity(&$database, &$current_user, &$log, &$values, $is_new, &$element = NULL)
        {
            // first, we let all parent classes to check the values
            parent::check_values_validity($database, $current_user, $log, $values, $is_new, $element);

            // optimize attribute "website"
            $values['website'] = trim($values['website']);
            if ((strlen($values['website']) > 0) && (mb_strpos($values['website'], '://') === false))  // if there is no protocol defined,
                $values['website'] = 'http://'.$values['website'];                                     // add "http://" to the begin

            // optimize attribute "auto_product_url"
            $values['auto_product_url'] = trim($values['auto_product_url']);
            if ((strlen($values['auto_product_url']) > 0) && (mb_strpos($values['auto_product_url'], '://') === false))  // if there is no protocol defined,
                $values['auto_product_url'] = 'http://'.$values['auto_product_url'];                                     // add "http://" to the begin
        }

    }

?>
