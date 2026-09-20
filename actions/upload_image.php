<?php
// Returns the existing path when no new file is selected. This keeps edit forms simple.
function upload_image($field_name, $current_image = '') {
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] === UPLOAD_ERR_NO_FILE) {
        return $current_image;
    }
    if ($_FILES[$field_name]['error'] !== UPLOAD_ERR_OK || $_FILES[$field_name]['size'] > 5 * 1024 * 1024) {
        return false;
    }
    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    $file_type = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field_name]['tmp_name']);
    if (!isset($allowed_types[$file_type])) {
        return false;
    }
    $upload_folder = __DIR__ . '/../assets/uploads/';
    if (!is_dir($upload_folder)) {
        mkdir($upload_folder, 0755, true);
    }
    $file_name = uniqid('rongin_', true) . '.' . $allowed_types[$file_type];
    if (!move_uploaded_file($_FILES[$field_name]['tmp_name'], $upload_folder . $file_name)) {
        return false;
    }
    return '/RonginBD/assets/uploads/' . $file_name;
}


function delete_image($image_path) {
    if ($image_path === '' || $image_path === null) {
        return;
    }
    $upload_folder = __DIR__ . '/../assets/uploads/';
    $file_path = $upload_folder . basename($image_path);
    if (is_file($file_path)) {
        unlink($file_path);
    }
}
?>