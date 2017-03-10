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

    /*
     * Please note:
     *  The files "edit_categories.php", "edit_footprints.php", "edit_manufacturers.php",
     *  "edit_suppliers.php", "edit_devices.php", "edit_storelocations.php" and "edit_filetypes.php"
     *  are quite similar.
     *  If you make changes in one of them, please check if you should change the other files too.
     */

    include_once('start_session.php');

    $messages = array();
    $fatal_error = false; // if a fatal error occurs, only the $messages will be printed, but not the site content

    /********************************************************************************
    *
    *   Evaluate $_REQUEST
    *
    *   Notes:
    *       - "$selected_id == 0" means that we will show the form for creating a new supplier
    *       - the $new_* variables contains the new values after editing an existing
    *           or creating a new supplier
    *
    *********************************************************************************/

    $selected_id          = isset($_REQUEST['selected_id'])      ? (integer)$_REQUEST['selected_id']     : 0;
    $new_name             = isset($_REQUEST['name'])             ? (string)$_REQUEST['name']             : '';
    $new_parent_id        = isset($_REQUEST['parent_id'])        ? (integer)$_REQUEST['parent_id']       : 0;
    $new_address          = isset($_REQUEST['address'])          ? (string)$_REQUEST['address']          : '';
    $new_phone_number     = isset($_REQUEST['phone_number'])     ? (string)$_REQUEST['phone_number']     : '';
    $new_fax_number       = isset($_REQUEST['fax_number'])       ? (string)$_REQUEST['fax_number']       : '';
    $new_email_address    = isset($_REQUEST['email_address'])    ? (string)$_REQUEST['email_address']    : '';
    $new_website          = isset($_REQUEST['website'])          ? (string)$_REQUEST['website']          : '';
    $new_auto_product_url = isset($_REQUEST['auto_product_url']) ? (string)$_REQUEST['auto_product_url'] : '';
    $add_more             = isset($_REQUEST['add_more']);

    $action = 'default';
    if (isset($_REQUEST["add"]))                {$action = 'add';}
    if (isset($_REQUEST["delete"]))             {$action = 'delete';}
    if (isset($_REQUEST["delete_confirmed"]))   {$action = 'delete_confirmed';}
    if (isset($_REQUEST["apply"]))              {$action = 'apply';}

    /********************************************************************************
    *
    *   Initialize Objects
    *
    *********************************************************************************/

    $html = new HTML($config['html']['theme'], $config['html']['custom_css'], _('Lieferanten'));

    try
    {
        $database           = new Database();
        $log                = new Log($database);
        $current_user       = new User($database, $current_user, $log, 1); // admin
        $root_supplier      = new Supplier($database, $current_user, $log, 0);

        if ($selected_id > 0)
            $selected_supplier = new Supplier($database, $current_user, $log, $selected_id);
        else
            $selected_supplier = NULL;
    }
    catch (Exception $e)
    {
        $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
        $fatal_error = true;
    }

    /********************************************************************************
    *
    *   Execute actions
    *
    *********************************************************************************/

    if ( ! $fatal_error)
    {
        switch ($action)
        {
            case 'add':
                try
                {
                    $new_supplier = Supplier::add(  $database, $current_user, $log, $new_name,
                                                    $new_parent_id, $new_address, $new_phone_number,
                                                    $new_fax_number, $new_email_address, $new_website,
                                                    $new_auto_product_url);

                    if ( ! $add_more)
                    {
                        $selected_supplier = $new_supplier;
                        $selected_id = $selected_supplier->get_id();
                    }
                }
                catch (Exception $e)
                {
                    $messages[] = array('text' => _('Der neue Lieferant konnte nicht angelegt werden!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
                }
                break;

            case 'delete':
                try
                {
                    if ( ! is_object($selected_supplier))
                        throw new Exception(_('Es ist kein Lieferant markiert oder es trat ein Fehler auf!'));

                    $parts = $selected_supplier->get_parts();
                    $count = count($parts);

                    if ($count > 0)
                    {
                        $messages[] = array('text' => sprintf(_('Es gibt noch %d Bauteile mit diesem Lieferanten, '.
                                                'daher kann der Lieferant nicht gelöscht werden.'),$count), 'strong' => true, 'color' => 'red');
                    }
                    else
                    {
                        $messages[] = array('text' => sprintf(_('Soll der Lieferant "%s'.
                                                        '" wirklich unwiederruflich gelöscht werden?'), $selected_supplier->get_full_path()), 'strong' => true, 'color' => 'red');
                        $messages[] = array('text' => _('<br>Hinweise:'), 'strong' => true);
                        $messages[] = array('text' => _('&nbsp;&nbsp;&bull; Es gibt keine Bauteile, die diesen Lieferanten zugeordnet haben.'));
                        $messages[] = array('text' => _('&nbsp;&nbsp;&bull; Beinhaltet dieser Lieferant noch Unterlieferanten, dann werden diese eine Ebene nach oben verschoben.'));
                        $messages[] = array('html' => '<input type="hidden" name="selected_id" value="'.$selected_supplier->get_id().'">');
                        $messages[] = array('html' => '<input type="submit" class="btn btn-default" name="" value="'._('Nein, nicht löschen').'">', 'no_linebreak' => true);
                        $messages[] = array('html' => '<input type="submit" class="btn btn-danger" name="delete_confirmed" value="'._('Ja, Lieferant löschen').'">');
                    }
                }
                catch (Exception $e)
                {
                    $messages[] = array('text' => _('Es trat ein Fehler auf!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
                }
                break;

            case 'delete_confirmed':
                try
                {
                    if ( ! is_object($selected_supplier))
                        throw new Exception(_('Es ist kein Lieferant markiert oder es trat ein Fehler auf!'));

                    $selected_supplier->delete();
                    $selected_supplier = NULL;
                }
                catch (Exception $e)
                {
                    $messages[] = array('text' => _('Der Lieferant konnte nicht gelöscht werden!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
                }
                break;

            case 'apply':
                try
                {
                    if ( ! is_object($selected_supplier))
                        throw new Exception(_('Es ist kein Lieferant markiert oder es trat ein Fehler auf!'));

                    $selected_supplier->set_attributes(array(   'name'             => $new_name,
                                                                'parent_id'        => $new_parent_id,
                                                                'address'          => $new_address,
                                                                'phone_number'     => $new_phone_number,
                                                                'fax_number'       => $new_fax_number,
                                                                'email_address'    => $new_email_address,
                                                                'website'          => $new_website,
                                                                'auto_product_url' => $new_auto_product_url));
                }
                catch (Exception $e)
                {
                    $messages[] = array('text' => _('Die neuen Werte konnten nicht gespeichert werden!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: '.nl2br($e->getMessage())), 'color' => 'red');
                }
                break;
        }
    }

    /********************************************************************************
    *
    *   Set the rest of the HTML variables
    *
    *********************************************************************************/

    $html->set_variable('add_more', $add_more, 'boolean');

    if (! $fatal_error)
    {
        try
        {
            if (is_object($selected_supplier))
            {
                $parent_id = $selected_supplier->get_parent_id();
                $html->set_variable('id', $selected_supplier->get_id(), 'integer');
                $html->set_variable('name', $selected_supplier->get_name(), 'string');
                $html->set_variable('address', $selected_supplier->get_address(), 'string');
                $html->set_variable('phone_number', $selected_supplier->get_phone_number(), 'string');
                $html->set_variable('fax_number', $selected_supplier->get_fax_number(), 'string');
                $html->set_variable('email_address', $selected_supplier->get_email_address(), 'string');
                $html->set_variable('website', $selected_supplier->get_website(), 'string');
                $html->set_variable('auto_product_url', $selected_supplier->get_auto_product_url(NULL), 'string');
            }
            elseif ($action == 'add')
            {
                $parent_id = $new_parent_id;
            }
            else
            {
                $parent_id = 0;
            }

            $supplier_list = $root_supplier->build_html_tree($selected_id, true, false);
            $html->set_variable('supplier_list', $supplier_list, 'string');

            $parent_supplier_list = $root_supplier->build_html_tree($parent_id, true, true);
            $html->set_variable('parent_supplier_list', $parent_supplier_list, 'string');
        }
        catch (Exception $e)
        {
            $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red', );
            $fatal_error = true;
        }
    }

    /********************************************************************************
    *
    *   Generate HTML Output
    *
    *********************************************************************************/

    $reload_link = $fatal_error ? 'edit_suppliers.php' : '';    // an empty string means that the...
    $html->print_header($messages, $reload_link);               // ...reload-button won't be visible

    if (! $fatal_error)
        $html->print_template('edit_suppliers');

    $html->print_footer();

?>
