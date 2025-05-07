<?
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Casino\FileModel;

class FileController extends BaseController
{
    use ResponseTrait;

    private $FileModel;

    public function __construct()
    {
        $this->FileModel = new FileModel();
    }

    /**
     * 顯示檔案
     * @param int $fileId
     * @return void
     */
    public function show($fileId)
    {
        $fileData = $this->FileModel->fetchFile($fileId);

        $path = WRITEPATH . $fileData['path'];

        if (!is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('File not found.');
        }

        $mimeType = mime_content_type($path);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setBody(file_get_contents($path));
    }
}
