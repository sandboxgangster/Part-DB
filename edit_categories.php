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
    *       - "$selected_id == 0" means that we will show the form for creating a new category
    *       - the $new_* variables contains the new values after editing an existing
    *           or creating a new category
    *
    *********************************************************************************/

    $selected_id                = isset($_REQUEST['selected_id'])   ? (integer)$_REQUEST['selected_id'] : 0;
    $new_name                   = isset($_REQUEST['name'])          ? (string)$_REQUEST['name']         : '';
    $new_parent_id              = isset($_REQUEST['parent_id'])     ? (integer)$_REQUEST['parent_id']   : 0;
    $new_disable_footprints     = isset($_REQUEST['disable_footprints']);
    $new_disable_manufacturers  = isset($_REQUEST['disable_manufacturers']);
    $new_disable_autodatasheets = isset($_REQUEST['disable_autodatasheets']);
    $add_more                   = isset($_REQUEST['add_more']);

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

    $html = new HTML($config['html']['theme'], $config['html']['custom_css'], _('Kategorien'));

    try
    {
        $database           = new Database();
        $log                = new Log($database);
        $current_user       = new User($database, $current_user, $log, 1); // admin
        $root_category      = new Category($database, $current_user, $log, 0);

        if ($selected_id > 0)
            $selected_category = new Category($database, $current_user, $log, $selected_id);
        else
            $selected_category = NULL;
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
                    $new_category = Category::add(  $database, $current_user, $log, $new_name,
                                                    $new_parent_id, $new_disable_footprints,
                                                    $new_disable_manufacturers, $new_disable_autodatasheets);

                    $html->set_variable('refresh_navigation_frame', true, 'boolean');

                    if ( ! $add_more)
                    {
                        $selected_category = $new_category;
                        $selected_id = $selected_category->get_id();
                    }
                }
                catch (Exception $e)
                {
                    $messages[] = array('text' => _('Die neue Kategorie konnte nicht angelegt werden!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
                }
                break;

            case 'delete':
                try
                {
                    if ( ! is_object($selected_category))
                        throw new Exception(_('Es ist keine Kategorie markiert oder es trat ein Fehler auf!'));

                    $parts = $selected_category->get_parts();
                    $count = count($parts);

                    if ($count > 0)
                    {
                        $messages[] = array('text' => sprintf(_('Es gibt noch %d Bauteile in dieser Kategorie, '.
                                            'daher kann die Kategorie nicht gelöscht werden.'), $count), 'strong' => true, 'color' => 'red');
                    }
                    else
                    {
                        /*
                        $messages[] = array('text' => sprintf(_('Soll die Kategorie "%s'.
                                                        '" wirklich unwiederruflich gelöscht werden?'), $selected_category->get_full_path()), 'strong' => true, 'color' => 'red');
                        $messages[] = array('text' => '<br>'._('Hinweise:'), 'strong' => true);
                        $messages[] = array('text' => '&nbsp;&nbsp;&bull; '._('Es gibt keine Bauteile in dieser Kategorie.'));
                        $messages[] = array('text' => '&nbsp;&nbsp;&bull; '._('Beinhaltet diese Kategorie noch Unterkategorien, dann werden diese eine Ebene nach oben verschoben.'));
                        $messages[] = array('html' => '<input type="hidden" name="selected_id" value="'.$selected_category->get_id().'">');
                        $messages[] = array('html' => '<input class="btn btn-default" type="submit" name="" value="'._('Nein, nicht löschen').'">', 'no_linebreak' => true);
                        $messages[] = array('html' => '<input class="btn btn-danger" type="submit" name="delete_confirmed" value="'._('Ja, Kategorie löschen').'">');
                        */

                        $notes[] = _('Es gibt keine Bauteile in dieser Kategorie.');
                        $notes[] = _('Beinhaltet diese Kategorie noch Unterkategorien, dann werden diese eine Ebene nach oben verschoben.');
                        $title = sprintf(_('Soll die Kategorie "%s'.
                            '" wirklich unwiederruflich gelöscht werden?'), $selected_category->get_full_path());
                        $dialog = generate_delete_dialog($selected_category->get_id(), $title, $notes, _('Ja, Kategorie löschen'), _('Nein, nicht löschen'));
                        $messages = array_merge($messages, $dialog);


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
                    if ( ! is_object($selected_category))
                        throw new Exception(_('Es ist keine Kategorie markiert oder es trat ein Fehler auf!'));

                    $selected_category->delete();
                    $selected_category = NULL;

                    $html->set_variable('refresh_navigation_frame', true, 'boolean');
                }
                catch (Exception $e)
                {
                    $messages[] = array('text' => _('Die Kategorie konnte nicht gelöscht werden!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
                }
                break;

            case 'apply':
                try
                {
                    if ( ! is_object($selected_category))
                        throw new Exception(_('Es ist keine Kategorie markiert oder es trat ein Fehler auf!'));

                    $selected_category->set_attributes(array('name'                     => $new_name,
                                                             'parent_id'                => $new_parent_id,
                                                             'disable_footprints'       => $new_disable_footprints,
                                                             'disable_manufacturers'    => $new_disable_manufacturers,
                                                             'disable_autodatasheets'   => $new_disable_autodatasheets));

                    $html->set_variable('refresh_navigation_frame', true, 'boolean');
                }
                catch (Exception $e)
                {
                    $messages[] = array('text' => _('Die neuen Werte konnten nicht gespeichert werden!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
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
            if (is_object($selected_category))
            {
                $parent_id = $selected_category->get_parent_id();
                $html->set_variable('id', $selected_category->get_id(), 'integer');
                $name = $selected_category->get_name();

                $disable_footprints = $selected_category->get_disable_footprints(true);
                $disable_manufacturers = $selected_category->get_disable_manufacturers(true);
                $disable_autodatasheets = $selected_category->get_disable_autodatasheets(true);

                $html->set_variable('parent_disable_footprints', ($selected_category->get_disable_footprints(true)
                                                                    && ( ! $selected_category->get_disable_footprints(false))), 'boolean');
                $html->set_variable('parent_disable_manufacturers', ($selected_category->get_disable_manufacturers(true)
                                                                        && ( ! $selected_category->get_disable_manufacturers(false))), 'boolean');
                $html->set_variable('parent_disable_autodatasheets', ($selected_category->get_disable_autodatasheets(true)
                                                                        && ( ! $selected_category->get_disable_autodatasheets(false))), 'boolean');
            }
            elseif ($action == 'add')
            {
                $parent_id = $new_parent_id;
                $name = $new_name;
                $disable_footprints = $new_disable_footprints;
                $disable_manufacturers = $new_disable_manufacturers;
                $disable_autodatasheets = $new_disable_autodatasheets;
            }
            else
            {
                $parent_id = 0;
                $name = '';
                $disable_footprints = false;
                $disable_manufacturers = false;
                $disable_autodatasheets = false;
            }

            $html->set_variable('name', $name, 'string');
            $html->set_variable('disable_footprints', $disable_footprints, 'boolean');
            $html->set_variable('disable_manufacturers', $disable_manufacturers, 'boolean');
            $html->set_variable('disable_autodatasheets', $disable_autodatasheets, 'boolean');

            $category_list = $root_category->build_html_tree($selected_id, true, false);
            $html->set_variable('category_list', $category_list, 'string');

            $parent_category_list = $root_category->build_html_tree($parent_id, true, true);
            $html->set_variable('parent_category_list', $parent_category_list, 'string');
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

    $reload_link = $fatal_error ? 'edit_categories.php' : '';    // an empty string means that the...
    $html->print_header($messages, $reload_link);                // ...reload-button won't be visible

    if (! $fatal_error)
        $html->print_template('edit_categories');

    $html->print_footer();

?>
