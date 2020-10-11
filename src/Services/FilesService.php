<?php


namespace Blog\Services;

use \finfo;

class FilesService
{
    public function upload(array $tempFile, array $rules, string $name)
    {
        $upload = [
            'errors' => [],
            'success' => false,
            'filename' => '',
        ];
        $ext = '';

        // Undefined | Multiple Files | $_FILES Corruption Attack
        if (!isset($tempFile['error']) || is_array($tempFile['error'])) {
            $upload['errors'][] = "Erreur de paramètres sur le fichier \"" . $name . "\"";
            return $upload;
        }

        // Check errors
        if ($tempFile['error'] > 0) {
            $upload['errors'][] = "Erreur (" . $tempFile['error'] . ") lors du l'upload du fichier \"" . $name . "\"";
            return $upload;
        }

        // Check filesize
        if ($tempFile['size'] > ($rules['maxSize'] * 1048576)) {
            $upload['errors'][] = "Le fichier \"" . $name . "\" est trop lourd (max " . $rules['maxSize'] . "Mo";
            return $upload;
        }


        $type = $rules['type'];
        $ext = $this->checkType($type, $tempFile);

        if ($rules['type'] === 'image') {
            if (!$ext) {
                $upload['errors'][] = "Le fichier \"" . $name . "\" n'a pas le bon format (jpeg, png ou gif)";
                return $upload;
            }
            if (!$this->checkResolution($rules['minRes'], $rules['maxRes'], $tempFile['tmp_name'])) {
                $upload['errors'][] = "Le fichier \"" . $name . "\" n'a pas la bonne résolution (min : " . $rules['minRes'][0] . "*" . $rules['minRes'][1] . ", max : " . $rules['maxRes'][0] . "*" . $rules['maxRes'][1] . ")";
                return $upload;
            }
        }

        if ($rules['type'] === 'pdf') {
            if (!$ext) {
                $upload['errors'][] = "Le fichier \"" . $name . "\" n'a pas le bon format (pdf)";
                return $upload;
            }
        }

        if (!$this->processUpload($tempFile['tmp_name'], $rules, $name . "." . $ext)) {
            $upload['errors'][] = "Le fichier \"" . $name . "\" n'a pas put être uploadé.";
            return $upload;
        }

        if (empty($upload['errors'])) {
            $upload['success'] = true;
            $upload['filename'] = $name . "." . $ext;
            return $upload;
        }
        return $upload;
    }

    private function checkType ($type, $tempFile)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = [];

        if ($type === 'image') {
            $mime = [
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ];
        } elseif ($type === 'pdf') {
            $mime = [
                'pdf' => 'application/pdf',
            ];
        }

        if (false === $ext = array_search($finfo->file($tempFile['tmp_name']), $mime, true)) {
            return false;
        }

        return $ext;
    }

    private function checkResolution ($min, $max, $tempFile)
    {
        $imgInfos = getimagesize($tempFile);

        if ($imgInfos[0] < $min[0] || $imgInfos[1] < $min[1] || $imgInfos[0] > $max[0] || $imgInfos[1] > $max[1]) {
            return false;
        }

        return true;
    }

    private function processUpload ($tmpName, $rules, $name)
    {
        $target = $rules['target'];
        $folder = $rules['folder'];
        if (!is_dir("uploads/" . $target . $folder)) mkdir("uploads/" . $target . $folder);
        $destination = "uploads/" . $target . $folder . "/" . $name;
        if (isset($rules['old'])) {
            if (!$this->deleteFile($rules, $rules['old'])){
                return false;
            }
        }
        if (!$this->deleteFile($rules, $name)){
            return false;
        }
        if (!move_uploaded_file($tmpName, $destination)) {
            return false;
        }
        return true;
    }

    public function deleteFile(array $rules, string $name) {
        $path = "uploads/". $rules['target'] . $rules['folder'] . "/" . $name;

        if (file_exists($path)) {
            if (!unlink($path)) {
                return false;
            }
            return true;
        }
        return true;
    }

    public function rename(array $rules, string $old, string $new) {
        $old = "uploads/". $rules['target'] . $rules['folder'] . "/" . $old;
        $new = "uploads/". $rules['target'] . $rules['folder'] . "/" . $new;
        if (file_exists($old)) {
            return rename($old, $new);
        }
        return true;
    }
}