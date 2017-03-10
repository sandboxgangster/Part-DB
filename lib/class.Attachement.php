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
     * @file class.Attachement.php
     * @brief class Attachement
     *
     * @class Attachement
     * @brief All elements of this class are stored in the database table "attachements".
     * @author kami89
     */
    class Attachement extends NamedDBElement
    {
        /********************************************************************************
        *
        *   Calculated Attributes
        *
        *   Calculated attributes will be NULL until they are requested for first time (to save CPU time)!
        *   After changing an element attribute, all calculated data will be NULLed again.
        *   So: the calculated data will be cached.
        *
        *********************************************************************************/

        /** @brief (object) the element of this attachement (for example a "Part" object) */
        private $element          = NULL;
        /** (AttachementType) the type of this attachement */
        private $attachement_type = NULL;

        /********************************************************************************
        *
        *   Constructor / Destructor / reset_attributes()
        *
        *********************************************************************************/

        /**
         * @brief Constructor
         *
         * @param Database  &$database          reference to the Database-object
         * @param User      &$current_user      reference to the current user which is logged in
         * @param Log       &$log               reference to the Log-object
         * @param integer   $id                 ID of the attachement we want to get
         *
         * @throws Exception        if there is no such attachement in the database
         * @throws Exception        if there was an error
         */
        public function __construct(&$database, &$current_user, &$log, $id)
        {
            parent::__construct($database, $current_user, $log, 'attachements', $id);
        }

        /**
         * @copydoc DBElement::reset_attributes()
         */
        public function reset_attributes($all = false)
        {
            $this->element          = NULL;
            $this->attachement_type = NULL;

            parent::reset_attributes($all);
        }

        /********************************************************************************
        *
        *   Basic Methods
        *
        *********************************************************************************/

        /**
         * @brief Delete this attachement from database (and the associated file from harddisc if desired)
         *
         * @note This method overrides the same-named method from the parent class.
         *
         * @param boolean $delete_from_hdd      if true, and the associated file isn't used in other file records,
         *                                      the file will be deleted from harddisc drive too (!!)
         *
         * @throws Exception if the file exists and should be deleted, but cannot be deleted
         *                   (maybe not enought permissions)
         * @throws Exception if there was an error
         */
        public function delete($delete_from_hdd = false)
        {
            $filename = $this->get_filename();
            $must_file_delete = false;

            if (($delete_from_hdd) && (strlen($filename) > 0))
            {
                // we will delete the file only from HDD if there are no other "Attachement" objects with the same filename!
                $attachements = Attachement::get_attachements_by_filename($this->database, $this->current_user, $this->log, $filename);

                if ((count($attachements) <= 1) && (file_exists($filename)))
                {
                    // check if there are enought permissions to delete the file
                    if ( ! is_writable(dirname($filename)))
                    {
                        throw new Exception('Die Datei "'.$filename.'" kann nicht gelöscht werden, '.
                                            'da im übergeordneten Ordner keine Schreibrechte vorhanden sind!');
                    }

                    // all OK, file must be deleted after deleting the database record successfully
                    $must_file_delete = true;
                }
            }

            try
            {
                $transaction_id = $this->database->begin_transaction(); // start transaction

                // Set all "id_master_picture_attachement" in the table "parts" to NULL where the master picture is this attachement
                $query = 'SELECT id from parts WHERE id_master_picture_attachement=?';
                $query_data = $this->database->query($query, array($this->get_id()));

                foreach ($query_data as $row)
                {
                    $part = new Part($this->database, $this->current_user, $this->log, $row['id']);
                    $part->set_master_picture_attachement_id(NULL);
                }

                $this->get_element()->set_attributes(array()); // save element attributes to update its "last_modified"

                // Now we can delete the database record of this attachement
                parent::delete();

                // now delete the file (if desired)
                if ($must_file_delete)
                {
                    if ( ! unlink($filename))
                        throw new Exception('Die Datei "'.$filename.'" kann nicht von der Festplatte gelöscht '.
                                            "werden! \nÜberprüfen Sie, ob die nötigen Rechte vorhanden sind.");
                }

                $this->database->commit($transaction_id); // commit transaction
            }
            catch (Exception $e)
            {
                $this->database->rollback(); // rollback transaction

                // restore the settings from BEFORE the transaction
                $this->reset_attributes();

                throw new Exception("Der Dateianhang \"".$this->get_name()."\" konnte nicht entfernt werden!\nGrund: ".$e->getMessage());
            }
        }

        /**
         * @brief Check if this attachement is a picture (analyse the file's extension)
         *
         * @retval boolean      @li true if the file extension is a picture extension
         *                      @li otherwise false
         */
        public function is_picture()
        {
            $extension = pathinfo($this->get_filename(), PATHINFO_EXTENSION);

            // list all file extensions which are supported to display them by HTML code
            $picture_extensions = array('gif', 'png', 'jpg', 'jpeg', 'bmp', 'svg', 'tif');

            return in_array(strtolower($extension), $picture_extensions);
        }

        /********************************************************************************
        *
        *   Getters
        *
        *********************************************************************************/

        /**
         * @brief Get the element (for example a "Part" object)
         *
         * @retval object     the element of this attachement
         *
         * @throws Exception if there was an error
         */
        public function get_element()
        {
            if ( ! is_object($this->element))
            {
                $this->element = new $this->db_data['class_name'] ($this->database, $this->current_user,
                                                $this->log, $this->db_data['element_id']);
            }

            return $this->element;
        }

        /**
         * @brief Get the filename (absolute path from filesystem root, as a UNIX path [only slashes])
         *
         * @retval string   the filename as an absolute UNIX filepath from filesystem root
         */
        public function get_filename()
        {
            return str_replace('%BASE%', BASE, $this->db_data['filename']);
        }

        /**
         * @brief Get the show_in_table attribute
         *
         * @retval boolean      @li true means, this attachement will be listed in the "Attachements" column of the HTML tables
         *                      @li false means, this attachement won't be listed in the "Attachements" column of the HTML tables
         */
        public function get_show_in_table()
        {
            return $this->db_data['show_in_table'];
        }

        /**
         * @brief Get the type of this attachement
         *
         * @retval AttachementType     the type of this attachement
         *
         * @throws Exception if there was an error
         */
        public function get_type()
        {
            if ( ! is_object($this->attachement_type))
            {
                $this->attachement_type = new AttachementType(  $this->database, $this->current_user,
                                                                $this->log, $this->db_data['type_id']);
            }

            return $this->attachement_type;
        }

        /********************************************************************************
        *
        *   Setters
        *
        *********************************************************************************/

        /**
         * @brief Set the filename
         *
         * @note    The filename will not be checked, it's not really important that the filename is valid...
         *          For this reason we have the method Attachement::get_invalid_filename_attachements() :-)
         *
         * @param string $new_filename      @li the new filename (absolute path from filesystem root as a UNIX path [only slashes]!!)
         *                                  @li see also lib.functions.php::to_unix_path()
         *
         * @warning     It's really important that you pass the whole (UNIX) path from filesystem root!
         *              If the file is located in the base directory of Part-DB, the base path
         *              will be automatically replaced with a placeholder before write it in the database.
         *              This way, the filenames are still correct if the installation directory
         *              of Part-DB is moved.
         *
         * @throws Exception if there was an error
         */
        public function set_filename($new_filename)
        {
            $this->set_attributes(array('filename' => $new_filename));
        }

        /**
         * @brief Set the attachement type ID
         *
         * @param integer $new_type_id      the ID of the new attachement type
         *
         * @throws Exception if the new type ID is not valid
         * @throws Exception if there was an error
         */
        public function set_type_id($new_type_id)
        {
            $this->set_attributes(array('type_id' => $new_type_id));
        }

        /********************************************************************************
        *
        *   Static Methods
        *
        *********************************************************************************/

        /**
         * @brief Get all Attachement-objects with a specific filename
         *
         * @param Database  &$database          reference to the Database-object
         * @param User      &$current_user      reference to the current user which is logged in
         * @param Log       &$log               reference to the Log-object
         * @param string    $filename           the exact filename with the whole path from filesystem root as a UNIX path!
         *                                      (see Attachement::set_filename())
         *
         * @retval array    all attachements as a one-dimensional array of "Attachement"-objects, sorted by their names
         *
         * @throws Exception if there was an error
         */
        public static function get_attachements_by_filename(&$database, &$current_user, &$log, $filename)
        {
            $attachements = array();

            // if the path is relative, we will make it absolute, but you should always use absolute paths anyway!
            // Then we replace the path of the Part-DB installation directory (Constant "BASE") with a placeholder ("%BASE%")
            $filename_2 = str_replace(BASE, '%BASE%', trim($filename));

            $query =    'SELECT id FROM attachements '.
                        'WHERE filename=? OR filename=? '.
                        'ORDER BY name ASC';
            // we will search for both, the original filename and the filename with replaced base-path
            $query_data = $database->query($query, array($filename, $filename_2));

            foreach ($query_data as $row)
                $attachements[] = new Attachement($database, $current_user, $log, $row['id']);

            return $attachements;
        }

        /**
         * @brief Get all attachements with invalid filename (file does not exist)
         *
         * @note Empty filenames are NOT valid (a file without filename makes no sense)!
         *
         * @param Database  &$database          reference to the Database-object
         * @param User      &$current_user      reference to the current user which is logged in
         * @param Log       &$log               reference to the Log-object
         *
         * @retval array    all attachements as a one-dimensional array of "Attachement"-objects, sorted by their names
         *
         * @throws Exception if there was an error
         */
        public static function get_invalid_filename_attachements(&$database, &$current_user, &$log)
        {
            $attachements = array();

            $query =    'SELECT id, filename FROM attachements '.
                        'ORDER BY name ASC';
            $query_data = $database->query($query);

            foreach ($query_data as $row)
            {
                if ( ! file_exists(str_replace('%BASE%', BASE, $row['filename'])))
                    $attachements[] = new Attachement($database, $current_user, $log, $row['id']);
            }

            return $attachements;
        }

        /**
         * @copydoc DBElement::check_values_validity()
         */
        public static function check_values_validity(&$database, &$current_user, &$log, &$values, $is_new, &$element = NULL)
        {
            // first, we set the basename as the name if the name is empty
            $values['name'] = trim($values['name']);
            if (strlen($values['name']) == 0)
                $values['name'] = basename($values['filename']);

            // then we let all parent classes to check the values
            parent::check_values_validity($database, $current_user, $log, $values, $is_new, $element);

            // set boolean attributes
            settype($values['show_in_table'], 'boolean');

            // check "type_id"
            try
            {
                // type_id == 0 or NULL means "no attachement type", and this is not allowed!
                if ($values['type_id'] == 0)
                    throw new Exception('"type_id" ist Null!');

                $attachement_type = new AttachementType($database, $current_user, $log, $values['type_id']);
            }
            catch (Exception $e)
            {
                debug('warning', 'Ungültige "type_id": "'.$values['type_id'].'"'.
                        "\n\nUrsprüngliche Fehlermeldung: ".$e->getMessage(),
                        __FILE__, __LINE__, __METHOD__);
                throw new Exception('Der gewählte Dateityp existiert nicht!');
            }

            // check "class_name"
            $supported_classes = array('Part'); // to be continued (step by step)...
            if ( ! in_array($values['class_name'], $supported_classes))
            {
                debug('error', 'Die Klasse "'.$values['class_name'].'" unterstützt (noch) keine Dateianhänge!',
                        __FILE__, __LINE__, __METHOD__);
                throw new Exception('Ungültiger Klassenname: "'.$values['class_name'].'"');
            }

            // check "element_id"
            try
            {
                // element_id == 0 is not allowed!
                if ($values['element_id'] == 0)
                    throw new Exception('"element_id" ist Null!');

                $element = new $values['class_name'] ($database, $current_user, $log, $values['element_id']);
                $element->set_attributes(array()); // save element attributes to update its "last_modified"
            }
            catch (Exception $e)
            {
                debug('warning', 'Ungültige "element_id"/"class_name": "'.$values['element_id'].'"/"'.
                        $values['class_name'].'"'."\n\nUrsprüngliche Fehlermeldung: ".$e->getMessage(),
                        __FILE__, __LINE__, __METHOD__);
                throw new Exception('Das gewählte Element existiert nicht!');
            }

            // trim $values['filename']
            $values['filename'] = trim($values['filename']);

            // empty filenames are not allowed!
            if (strlen($values['filename']) == 0)
                throw new Exception('Der Dateiname ist leer, das ist nicht erlaubt!');

            // check if "filename" is a valid (absolute and UNIX) filepath
            if ( ! is_path_absolute_and_unix($values['filename']))
                throw new Exception('Der Dateipfad "'.$values['filename'].'" ist kein gültiger absoluter UNIX Dateipfad!');

            // we replace the path of the Part-DB installation directory (Constant "BASE") with a placeholder ("%BASE%")
            $values['filename'] = str_replace(BASE, '%BASE%', $values['filename']);
        }

        /**
         * @brief Get count of attachements
         *
         * @param Database &$database   reference to the Database-object
         *
         * @retval integer              count of attachements
         *
         * @throws Exception            if there was an error
         */
        public static function get_count(&$database)
        {
            if (get_class($database) != 'Database')
                throw new Exception('$database ist kein Database-Objekt!');

            return $database->get_count_of_records('attachements');
        }

        /**
         * @brief Create a new attachement
         *
         * @param Database  &$database          reference to the database object
         * @param User      &$current_user      reference to the user which is logged in
         * @param Log       &$log               reference to the Log-object
         * @param object    &$element           @li the element on which the file will be attached
         *                                      @li For supported elements see Attachement::check_values_validity()
         * @param integer   $type_id            the ID of the attachement type (see Attachement::set_type_id())
         * @param string    $filename           the filename of the new attachement (see Attachement::set_filename())
         * @param string    $name               the name of the new attachement (see Attachement::set_name())
         * @param boolean   $show_in_table      the "show_in_table" attribute of the new filename (see Attachement::set_show_in_table())
         *
         * @warning         You have to supply the full path from filesystem root in $filename!!
         *                  For more details see Attachement::set_filename().
         *
         * @retval Attachement  the new attachement
         *
         * @throws Exception    if (this combination of) values is not valid
         * @throws Exception    if there was an error
         *
         * @see DBElement::add()
         */
        public static function add(&$database, &$current_user, &$log, &$element, $type_id,
                                    $filename, $name = '', $show_in_table = false)
        {
            if ( ! is_object($element))
                throw new Exception('$element ist kein Objekt!');

            return parent::add($database, $current_user, $log, 'attachements',
                                array(  'name'              => $name,
                                        'class_name'        => get_class($element),
                                        'element_id'        => $element->get_id(),
                                        'type_id'           => $type_id,
                                        'filename'          => $filename,
                                        'show_in_table'     => $show_in_table));
        }

    }

?>
