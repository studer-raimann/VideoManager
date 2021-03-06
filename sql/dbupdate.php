<#1>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
ilVideoManagerObject::updateDB();
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Subscription/class.vidmSubscription.php');
vidmSubscription::updateDB();
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerFolder.php');
require_once 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerVideoTree.php';
if($root_folder = ilVideoManagerFolder::__getRootFolder())
{
    $root_folder->setTitle('Video Manager');
    $root_folder->update();
}else{
    $root_folder = new ilVideoManagerFolder();
    $root_folder->setId(1);
    $root_folder->setTitle('Video Manager');
    $root_folder->create();
}

global $DIC;
$ilDB = $DIC->database();
if(!$ilDB->tableExists(ilVideoManagerVideoTree::TABLE_NAME))
{
    $fields = array(
        'tree' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'child' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'parent' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
        ),
        'lft' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'rgt' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'depth' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        )
    );
    $ilDB->createTable(ilVideoManagerVideoTree::TABLE_NAME, $fields);
}

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
$tree = new ilVideoManagerTree(1);
$tree->addTree($tree->getTreeId());
?>
<#2>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Count/class.vidmCount.php');
vidmCount::updateDB();
?>
<#3>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Config/class.vidmConfig.php');
vidmConfig::updateDB();
vidmConfig::setV(vidmConfig::F_ACTIVATE_SUBSCRIPTION, true);
vidmConfig::setV(vidmConfig::F_ACTIVATE_VIEW_LOG, true);
vidmConfig::setV(vidmConfig::F_ROLES, array( 2 ));
?>
<#4>
<?php
require_once "Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php";
global $DIC;
$ilDB = $DIC->database();
if (!$ilDB->tableColumnExists(ilVideoManagerObject::TABLE_NAME, 'hidden')) {
   $ilDB->addTableColumn(ilVideoManagerObject::TABLE_NAME, 'hidden', array(
       'type' => 'integer',
       'length' => 1,
       'notnull' => false,
   ));
}
if (!$ilDB->tableColumnExists(ilVideoManagerObject::TABLE_NAME, 'image_at_second')) {
    $ilDB->addTableColumn(ilVideoManagerObject::TABLE_NAME, 'image_at_second', array(
        'type' => 'integer',
        'length' => 8,
        'notnull' => false,
    ));
}
?>
