<?php
require_once "../config.php";
require_once "util/tdiscus.php";
require_once "util/threads.php";

use \Tsugi\Util\U;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
use \Tdiscus\Tdiscus;
use \Tdiscus\Threads;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);


$THREADS = new Threads();

$rest_path = U::rest_path();

$come_back = 'threadremove';
$thread_id = null;
$old_thread = null;
if ( isset($rest_path->action) && is_numeric($rest_path->action) ) {
    $thread_id = intval($rest_path->action);
    $old_thread = $THREADS->threadLoadForUpdate($thread_id);
}

if ( ! $old_thread ) {
    $_SESSION['error'] = __('Could not load thread');
    header( 'Location: '.addSession($TOOL_ROOT) ) ;
    return;
}

if ( count($_POST) > 0 ) {
    $retval = $THREADS->threadDelete($thread_id);
    if ( is_string($retval) ) {
        $_SESSION['error'] = $retval;
        header( 'Location: '.addSession($TOOL_ROOT . '/' . $come_back) ) ;
        return;
    }

    $_SESSION['success'] = __('Thread deleted');
    header( 'Location: '.addSession($TOOL_ROOT) ) ;
    return;
}

Tdiscus::header();

$menu = false;

$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
$OUTPUT->topNav($menu);
?>
<p><?= __("Title:") ?> <br/>
<?php
echo('<b>'.htmlentities($old_thread['title']).'</b></br>');
?>
</p>
<p><?= __("Description:") ?> <br/>
<?= $purifier->purify($old_thread['body']) ?>
</p>
<div id="delete-thread-div" title="<?= __("Delete thread") ?>" >
<form id="delete-thread-form" method="post">
<p>
<input type="submit" id="delete-thread-submit" value="<?= __('Delete') ?>" >
<input type="submit" id="delete-thread-cancel" value="<?= __('Cancel') ?>"
onclick='window.location.href="<?= addSession($TOOL_ROOT) ?>";return false;'
>
</p>
</form>
</div>
<?php

Tdiscus::footerStart();
?>
<script>
$(document).ready( function () {
    CKEDITOR.replace( 'editor' );
});
</script>
<?php
Tdiscus::footerEnd();
