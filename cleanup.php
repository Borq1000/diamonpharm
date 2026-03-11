<?php
$target = __DIR__ . '/create_storage.php';
if (file_exists($target)) {
    unlink($target) ? print('Deleted.') : print('Failed.');
} else {
    print('Already gone.');
}
unlink(__FILE__);
