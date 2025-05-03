<?php

namespace App\Models\Casino;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;

class FileModel extends Model
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('casino');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
    }

    /**
     * 儲存檔案
     * @param object $file 檔案物件
     * @param string $path 檔案路徑
     * @param string $type 檔案類型
     * @param int $size 檔案大小
     * @return int 新增的檔案 ID
     */
    public function saveFile(object $file, string $path): int
    {
        if (!$file->isValid()) {
            // log_message('error', 'Invalid file upload');
            return false;
        }

        $newName = $file->getRandomName();

        $file->move(WRITEPATH . 'uploads/' . $path, $newName);
        if (!$file->hasMoved()) {
            // log_message('error', 'Failed to move uploaded file');
            return false;
        }

        $this->db->table('files')->insert([
            'name'        => $file->getClientName(),
            'path'        => 'uploads/' . $path . '/' . $newName,
            'type'        => $file->getClientMimeType(),
            'size'        => $file->getSize(),
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insertID();
    }

    public function fetchFile($fileId)
    {
        return $this->db->table('files')
            ->where('id', $fileId)
            ->get()
            ->getRowArray();
    }
}