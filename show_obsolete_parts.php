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

    include_once('start_session.php');

    $messages = array();
    $fatal_error = false; // if a fatal error occurs, only the $messages will be printed, but not the site content

    /********************************************************************************
    *
    *   Evaluate $_REQUEST
    *
    *********************************************************************************/

    $show_no_orderdetails_parts = (isset($_REQUEST['show_no_orderdetails_parts'])) ? $_REQUEST['show_no_orderdetails_parts'] : false;

    $action = 'default';
    if (isset($_REQUEST['change_show_no_orderdetails']))    {$action = 'change_show_no_orderdetails';}

    /********************************************************************************
    *
    *   Initialize Objects
    *
    *********************************************************************************/

    $html = new HTML($config['html']['theme'], $config['html']['custom_css'], 'Nicht mehr erhältliche Teile');

    try
    {
        $database           = new Database();
        $log                = new Log($database);
        $current_user       = new User($database, $current_user, $log, 1); // admin
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
            case 'change_show_no_orderdetails':
                $reload_site = true;
                break;
        }
    }

    if (isset($reload_site) && $reload_site && ( ! $config['debug']['request_debugging_enable']))
    {
        // reload the site to avoid multiple actions by manual refreshing
        header('Location: show_obsolete_parts.php?show_no_orderdetails_parts='.($show_no_orderdetails_parts ? '1' : '0'));
    }

    /********************************************************************************
    *
    *   Generate Table
    *
    *********************************************************************************/

    if ( ! $fatal_error)
    {
        try
        {
            $parts = Part::get_obsolete_parts($database, $current_user, $log, $show_no_orderdetails_parts);
            $table_loop = Part::build_template_table_array($parts, 'obsolete_parts');
            $html->set_loop('table', $table_loop);
        }
        catch (Exception $e)
        {
            $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
            $fatal_error = true;
        }
    }

    /********************************************************************************
    *
    *   Set the rest of the HTML variables
    *
    *********************************************************************************/

    $html->use_javascript(array('popup'));

    if ( ! $fatal_error)
    {
        // obsolete parts
        $html->set_variable('show_no_orderdetails_parts',   $show_no_orderdetails_parts,            'boolean');

        // global stuff
        $html->set_variable('disable_footprints',           $config['footprints']['disable'],       'boolean');
        $html->set_variable('disable_manufacturers',        $config['manufacturers']['disable'],    'boolean');
        $html->set_variable('disable_auto_datasheets',      $config['auto_datasheets']['disable'],  'boolean');

        $html->set_variable('use_modal_popup',              $config['popup']['modal'],              'boolean');
        $html->set_variable('popup_width',                  $config['popup']['width'],              'integer');
        $html->set_variable('popup_height',                 $config['popup']['height'],             'integer');
    }

    /********************************************************************************
    *
    *   Generate HTML Output
    *
    *********************************************************************************/

    $html->print_header($messages);

    if (! $fatal_error)
        $html->print_template('show_obsolete_parts');

    $html->print_footer();
