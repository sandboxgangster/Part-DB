<?php
/* Smarty version 3.1.30, created on 2016-11-21 20:35:14
  from "C:\xampp\htdocs\part-db\templates\nextgen\startup.php\smarty_startup.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58334c72931ca7_82694372',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e4eae8eebf0a70dd43b7f76d48defab879bd5d63' => 
    array (
      0 => 'C:\\xampp\\htdocs\\part-db\\templates\\nextgen\\startup.php\\smarty_startup.tpl',
      1 => 1478957570,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_58334c72931ca7_82694372 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_locale')) require_once 'C:\\xampp\\htdocs\\part-db\\lib\\smarty\\plugins\\function.locale.php';
if (!is_callable('smarty_block_t')) require_once 'C:\\xampp\\htdocs\\part-db\\lib\\smarty\\plugins\\block.t.php';
echo smarty_function_locale(array('path'=>"nextgen/locale",'domain'=>"partdb"),$_smarty_tpl);?>

    <div class="jumbotron">
        <h1>Part-DB</h1>
        <?php if (isset($_smarty_tpl->tpl_vars['system_version_full']->value)) {?>
        <h3><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('t', array());
$_block_repeat1=true;
echo smarty_block_t(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
Version:<?php $_block_repeat1=false;
echo smarty_block_t(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
 <?php echo $_smarty_tpl->tpl_vars['system_version_full']->value;
if (isset($_smarty_tpl->tpl_vars['git_branch']->value)) {?>, Git: <?php echo $_smarty_tpl->tpl_vars['git_branch']->value;
if (isset($_smarty_tpl->tpl_vars['git_commit']->value)) {?>/<?php echo $_smarty_tpl->tpl_vars['git_commit']->value;
}
}?></h3>
        <?php }?>
        <h4><i>"NextGen"</i></h4>
    </div>
    
    <?php if (isset($_smarty_tpl->tpl_vars['database_update']->value)) {?>
        <?php if ($_smarty_tpl->tpl_vars['database_update']->value) {?>
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h2<?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('t', array());
$_block_repeat1=true;
echo smarty_block_t(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
>Datenbankupdate<?php $_block_repeat1=false;
echo smarty_block_t(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</h2>
            </div>
            <div class="panel-body">
            <b>Datenbank-Version <?php echo $_smarty_tpl->tpl_vars['db_version_current']->value;?>
 benötigt ein Update auf Version <?php echo $_smarty_tpl->tpl_vars['db_version_latest']->value;?>
.</b><br><br>
            <?php if (isset($_smarty_tpl->tpl_vars['disabled_autoupdate']->value)) {?>
            <?php if (isset($_smarty_tpl->tpl_vars['auto_disabled_autoupdate']->value)) {?>
                <p>Automatische Datenbankupdates wurden vorübergehend automatisch deaktiviert,
                da es sich um ein sehr umfangreiches Update handelt.</p>
            <?php } else { ?>
                <p>Automatische Datenbankupdates sind deaktiviert.</p>
            <?php }?>
            Updates bitte manuell durchführen: <a href="system_database.php">System -> Datenbank</a>
        <?php } else { ?>
            <?php echo $_smarty_tpl->tpl_vars['database_update_log']->value;?>

        <?php }?>
            </div>
        </div>
        <?php }?>
    <?php }?>

<?php if ($_smarty_tpl->tpl_vars['display_warning']->value) {?>
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h2 class="red">Achtung!</h2>
            </div>
        <div class="panel-body">
            Bitte beachten Sie, dass vor der Verwendung der Datenbank mindestens<br>
            <blockquote><?php echo $_smarty_tpl->tpl_vars['missing_category']->value;?>
eine <a href="edit_categories.php" target="content_frame">Kategorie</a> </blockquote>hinzufügt werden muss.<br><br>
            Um das Potential der Suchfunktion zu nutzen, wird empfohlen
            <blockquote><?php echo $_smarty_tpl->tpl_vars['missing_storeloc']->value;?>
einen <a href="edit_storelocations.php">Lagerort</a> </blockquote>
            <blockquote><?php echo $_smarty_tpl->tpl_vars['missing_footprint']->value;?>
einen <a href="edit_footprints.php">Footprint</a> </blockquote>
            <blockquote><?php echo $_smarty_tpl->tpl_vars['missing_supplier']->value;?>
und einen <a href="edit_suppliers.php">Lieferanten</a> </blockquote>
            anzugeben.
        </div>
    </div>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['broken_filename_footprints']->value) {?>
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h2 class="red">Achtung!</h2>
            </div>
        <div class="panel-body">
        <font color="red">In Ihrer Datenbank gibt es Footprints, die einen fehlerhaften Dateinamen hinterlegt haben.
        Dies kann durch ein Datenbankupdate, ein Update von Part-DB, oder durch nicht mehr existierende Dateien ausgelöst worden sein.
        <br>
        Sie können dies unter <a href="edit_footprints.php">Bearbeiten/Footprints</a> (ganz unten, "Fehlerhafte Dateinamen") korrigieren.
        </font>
    </div>
    </div>
<?php }?>

<?php echo $_smarty_tpl->tpl_vars['banner']->value;?>


<div class="panel panel-primary">
    <div class="panel-heading">
        <h3><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('t', array());
$_block_repeat1=true;
echo smarty_block_t(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
Lizenz<?php $_block_repeat1=false;
echo smarty_block_t(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</h3>
    </div>
    <div class="panel-body">
       <!-- Doesnt work! Paypal has changed API?
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="GE4ABWP3JUHLL">
            <input type="image" src="https://www.paypalobjects.com/de_DE/CH/i/btn/btn_donateCC_LG.gif" border="0" name="submit" align="right" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.">
            <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
        </form>
         -->

        Part-DB, Copyright &copy; 2005 of <strong>Christoph Lechner</strong>. Part-DB is published under the <strong>GPL</strong>, so it comes with <strong>ABSOLUTELY NO WARRANTY</strong>, click <a href="<?php echo $_smarty_tpl->tpl_vars['relative_path']->value;?>
readme/gpl.txt">here</a> for details. This is free software, and you are welcome to redistribute it under certain conditions. Click <a href="<?php echo $_smarty_tpl->tpl_vars['relative_path']->value;?>
readme/gpl.txt">here</a> for details.<br>
        <br>
        <strong><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('t', array());
$_block_repeat1=true;
echo smarty_block_t(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
Projektseite:<?php $_block_repeat1=false;
echo smarty_block_t(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</strong> Downloads, Bugreports, ToDo-Liste usw. gibts auf der <a target="_blank" href="https://github.com/sandboxgangster/Part-DB">GitHub Projektseite</a><br>
        <strong>Forum:</strong> Für Fragen rund um die Part-DB gibt es einen Thread auf <a target="_blank" href="https://www.mikrocontroller.net/topic/305023">mikrocontroller.net</a><br>
        <strong>Wiki:</strong> Weitere Informationen gibt es im <a target="_blank" href="http://www.mikrocontroller.net/articles/Part-DB_RW_-_Lagerverwaltung">mikrocontroller.net Artikel</a><br>
        <br>
        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('t', array());
$_block_repeat1=true;
echo smarty_block_t(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
Initiator:<?php $_block_repeat1=false;
echo smarty_block_t(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
 <strong>Christoph Lechner</strong> - <a target="_blank" href="http://www.cl-projects.de/">http://www.cl-projects.de/</a><br>
        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('t', array());
$_block_repeat1=true;
echo smarty_block_t(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
Autor seit 2009:<?php $_block_repeat1=false;
echo smarty_block_t(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
 <strong>K. Jacobs</strong> - <a target="_blank" href="http://www.grautier.com/">http://grautier.com</a><br>
        <br>
        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('t', array());
$_block_repeat1=true;
echo smarty_block_t(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
Weitere Autoren:<?php $_block_repeat1=false;
echo smarty_block_t(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>

        <table class="table">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['authors']->value, 'author');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['author']->value) {
?>
            <tr><td><strong><?php echo $_smarty_tpl->tpl_vars['author']->value['name'];?>
</strong></td><td><?php echo $_smarty_tpl->tpl_vars['author']->value['role'];?>
</td></tr>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

        </table>
    </div>
</div>

<?php if (isset($_smarty_tpl->tpl_vars['rss_feed_loop']->value)) {?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('t', array());
$_block_repeat1=true;
echo smarty_block_t(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
Updates<?php $_block_repeat1=false;
echo smarty_block_t(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</h4>
    </div>
    <div class="panel-body">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['rss_feed_loop']->value, 'rss');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['rss']->value) {
?>
            <b><?php echo $_smarty_tpl->tpl_vars['rss']->value['title'];?>
</b><br>
            <?php echo $_smarty_tpl->tpl_vars['rss']->value['datetime'];?>
<br>
            <a href="<?php echo $_smarty_tpl->tpl_vars['rss']->value['link'];?>
" target="_blank"><?php echo $_smarty_tpl->tpl_vars['rss']->value['link'];?>
</a>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

    <br>
    </div>
</div>
<?php }?>
    
    
</div><?php }
}
