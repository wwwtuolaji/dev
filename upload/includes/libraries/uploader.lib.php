<?php

/**
 *    文件上传辅助类
 *
 *    @author    Garbin
 *    @usage    none
 */
class Uploader extends Object
{
    var $_file              = null;
    var $_allowed_file_type = null;
    var $_allowed_file_size = null;
    var $_root_dir          = null;

    /**
     *    添加由POST上来的文件
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function addFile($file)
    {
        if (!is_uploaded_file($file['tmp_name']))
        {
            return false;
        }
        $this->_file = $this->_get_uploaded_info($file);
    }

    /**
     *    设定允许添加的文件类型
     *
     *    @author    Garbin
     *    @param     string $type （小写）示例：gif|jpg|jpeg|png
     *    @return    void
     */
    function allowed_type($type)
    {
        $this->_allowed_file_type = explode('|', $type);
    }

    /**
     *    允许的大小
     *
     *    @author    Garbin
     *    @param     mixed $size
     *    @return    void
     */
    function allowed_size($size)
    {
        $this->_allowed_file_size = $size;
    }
    function _get_uploaded_info($file)
    {
        $pathinfo = pathinfo($file['name']);
        $file['extension'] = $pathinfo['extension'];
        $file['filename']     = $pathinfo['basename'];
        if (!$this->_is_allowd_type($file['extension']))
        {
            $this->_error('not_allowed_type', $file['extension']);

            return false;
        }
        if (!$this->_is_allowd_size($file['size']))
        {
            $this->_error('not_allowed_size', $file['size']);

            return false;
        }

        return $file;
    }
    function _is_allowd_type($type)
    {
        if (!$this->_allowed_file_type)
        {
            return true;
        }
        return in_array(strtolower($type), $this->_allowed_file_type);
    }
    function _is_allowd_size($size)
    {
        if (!$this->_allowed_file_size)
        {
            return true;
        }

        return is_numeric($this->_allowed_file_size) ?
                ($size <= $this->_allowed_file_size) :
                ($size >= $this->_allowed_file_size[0] && $size <= $this->_allowed_file_size[1]);
    }
    /**
     *    获取上传文件的信息
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function file_info()
    {
        return $this->_file;
    }

    /**
     *    若没有指定root，则将会按照所指定的path来保存，但是这样一来，所获得的路径就是一个绝对或者相对当前目录的路径，因此用Web访问时就会有问题，所以大多数情况下需要指定
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function root_dir($dir)
    {
        $this->_root_dir = $dir;
    }
    function save($dir, $name = false)
    {
        if (!$this->_file)
        {
            return false;
        }
        if (!$name)
        {
            $name = $this->_file['filename'];
        }
        else
        {
            $name .= '.' . $this->_file['extension'];
        }
        $path = $dir . '/' . $name;

        return $this->move_uploaded_file($this->_file['tmp_name'], $path);
    }

    /**
     *    将上传的文件移动到指定的位置
     *
     *    @author    Garbin
     *    @param     string $src
     *    @param     string $target
     *    @return    bool
     */
    function move_uploaded_file($src, $target)
    {
        $abs_path = $this->_root_dir ? $this->_root_dir . '/' . $target : $target;
        $dirname = dirname($target);
        if (!ecm_mkdir(ROOT_PATH . '/' . $dirname))
        {
            $this->_error('dir_doesnt_exists');

            return false;
        }
        if (move_uploaded_file($src, $abs_path))
        {
            @chmod($abs_path, 0666);
            return $target;
        }
        else
        {
            return false;
        }
    }

    /**
     * 生成随机的文件名
     */
    function random_filename()
    {
        $seedstr = explode(" ", microtime(), 5);
        $seed    = $seedstr[0] * 10000;
        srand($seed);
        $random  = rand(1000,10000);

        return date("YmdHis", time()) . $random;
    }
}

/**
 *    FtpUploader
 *
 *    @author    Garbin
 *    @usage    none
 */
class FtpUploader extends Uploader
{
    var $_ftp_server = null;
    function __construct(&$_ftp_server)
    {
        $this->_ftp_server = $_ftp_server;
    }
    function move_uploaded_file($src, $target)
    {
        if (!$this->_ftp_server)
        {
            $this->_error('no_ftp_server');
            return false;
        }
        $dir = dirname($target);
        $this->_chdir($dir);

        return  $this->_ftp_server->put($src, basename($target)) ? $target : false;
    }
    function _chdir($dir)
    {
        restore_error_handler();

        $dirs = explode('/', $dir);
        if (empty($dirs))
        {
            return true;
        }
        /* 循环创建目录 */
        foreach ($dirs as $d)
        {
            if (!@$this->_ftp_server->chdir($d))
            {
                $this->_ftp_server->mkdir($d);
                $this->_ftp_server->chmod($d);
                $this->_ftp_server->chdir($d);
                $this->_ftp_server->put(ROOT_PATH . '/data/index.html', 'index.html');
            }
        }

        reset_error_handler();

        return true;
    }
}


/**
 * jjc文件上传
 */
class ImageUploadTool
{
    private $file;          //文件信息
    private $fileList;      //文件列表
    private $inputName;     //标签名称
    private $uploadPath;    //上传路径
    private $fileMaxSize;   //最大尺寸
    private $uploadFiles;   //上传文件
    //允许上传的文件类型
    private $allowExt = array('bmp', 'jpg', 'jpeg', 'png', 'gif');

    /**
     * ImageUploadTool constructor.
     * @param $inputName input标签的name属性
     * @param $uploadPath 文件上传路径
     */
    public function __construct($inputName, $uploadPath)
    {
        $this->inputName = $inputName;
        $this->uploadPath = $uploadPath;
        $this->fileList = array();
        $this->file = $file = array(
            'name' => null,
            'type' => null,
            'tmp_name' => null,
            'size' => null,
            'errno' => null,
            'error' => null
        );
    }

    /**
     * 设置允许上传的图片后缀格式
     * @param $allowExt 文件后缀数组
     */
    public function setAllowExt($allowExt)
    {
        if (is_array($allowExt)) {
            $this->allowExt = $allowExt;
        } else {
            $this->allowExt = array($allowExt);
        }
    }

    /**
     * 设置允许上传的图片规格
     * @param $fileMaxSize 最大文件尺寸
     */
    public function setMaxSize($fileMaxSize)
    {
        $this->fileMaxSize = $fileMaxSize;
    }

    /**
     * 获取上传成功的文件数组
     * @return mixed
     */
    public function getUploadFiles()
    {
        return $this->uploadFiles;
    }

    /**
     * 得到文件上传的错误信息
     * @return array|mixed
     */
    public function getErrorMsg()
    {
        if (count($this->fileList) == 0) {
            return $this->file['error'];
        } else {
            $errArr = array();
            foreach ($this->fileList as $item) {
                array_push($errArr, $item['error']);
            }
            return $errArr;
        }
    }

    /**
     * 初始化文件参数
     * @param $isList
     */
    private function initFile($isList)
    {
        if ($isList) {
            foreach ($_FILES[$this->inputName] as $key => $item) {
                for ($i = 0; $i < count($item); $i++) {
                    if ($key == 'error') {
                        $this->fileList[$i]['error'] = null;
                        $this->fileList[$i]['errno'] = $item[$i];
                    } else {
                        $this->fileList[$i][$key] = $item[$i];
                    }
                }
            }
        } else {
            $this->file['name'] = $_FILES[$this->inputName]['name'];
            $this->file['type'] = $_FILES[$this->inputName]['type'];
            $this->file['tmp_name'] = $_FILES[$this->inputName]['tmp_name'];
            $this->file['size'] = $_FILES[$this->inputName]['size'];
            $this->file['errno'] = $_FILES[$this->inputName]['error'];
        }
    }

    /**
     * 上传错误检查
     * @param $errno
     * @return null|string
     */
    private function errorCheck($errno)
    {
        switch ($errno) {
            case UPLOAD_ERR_OK:
                return null;
            case UPLOAD_ERR_INI_SIZE:
                return '文件尺寸超过服务器限制';
            case UPLOAD_ERR_FORM_SIZE:
                return '文件尺寸超过表单限制';
            case UPLOAD_ERR_PARTIAL:
                return '只有部分文件被上传';
            case UPLOAD_ERR_NO_FILE:
                return '没有文件被上传';
            case UPLOAD_ERR_NO_TMP_DIR:
                return '找不到临时文件夹';
            case UPLOAD_ERR_CANT_WRITE:
                return '文件写入失败';
            case UPLOAD_ERR_EXTENSION:
                return '上传被扩展程序中断';
        }
    }

    /**
     * 上传文件校验
     * @param $file
     * @throws Exception
     */
    private function fileCheck($file)
    {
        //图片上传过程是否顺利
        if ($file['errno'] != 0) {
            $error = $this->errorCheck($file['errno']);
            throw new Exception($error);
        }
        //图片尺寸是否符合要求
        if (!empty($this->fileMaxSize) && $file['size'] > $this->fileMaxSize) {
            throw new Exception('文件尺寸超过' . ($this->fileMaxSize / 1024) . 'KB');
        }
        //图片类型是否符合要求
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array($ext, $this->allowExt)) {
            throw new Exception('不符合要求的文件类型');
        }
        //图片上传方式是否为HTTP
        $file['tmp_name'] = str_replace('////', '//', $file['tmp_name']); 
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('文件不是通过HTTP方式上传的');
        }
        //图片是否可以读取
        if (!getimagesize($file['tmp_name'])) {
            throw new Exception('图片文件损坏');
        }
        //检查上传路径是否存在
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, null, true);
        }
    }

    /**
     * 单文件上传，成功返回true
     * @return bool
     */
    public function acceptSingleFile()
    {
        $this->initFile(false);
        try {
            $this->fileCheck($this->file);
            $md_name = md5(uniqid(microtime(true), true)) . '.' . pathinfo($this->file['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($this->file['tmp_name'], $this->uploadPath . $md_name)) {
                $this->uploadFiles = array($this->uploadPath . $md_name);
            } else {
                throw new Exception('文件上传失败');
            }
        } catch (Exception $e) {
            $this->file['error'] = $e->getMessage();
        } 
            if (file_exists($this->file['tmp_name'])) {
                unlink($this->file['tmp_name']);
            }
        
        return empty($this->file['error']) ? true : false;
    }

    /**
     * 多文件上传，全部成功返回true
     * @return bool
     */
    public function acceptMultiFile()
    {
        $this->initFile(true);
        $this->uploadFiles = array();
        for ($i = 0; $i < count($this->fileList); $i++) {
            try {
                $this->fileCheck($this->fileList[$i]);
                $ext = pathinfo($this->fileList[$i]['name'], PATHINFO_EXTENSION);
                $md_name = md5(uniqid(microtime(true), true)) . '.' . $ext;
                if (move_uploaded_file($this->fileList[$i]['tmp_name'], $this->uploadPath . $md_name)) {
                    array_push($this->uploadFiles, $this->uploadPath . $md_name);
                } else {
                    throw new Exception('文件上传失败');
                }
            } catch (Exception $e) {
                $this->fileList[$i]['error'] = $e->getMessage();
            } 
                if (file_exists($this->fileList[$i]['tmp_name'])) {
                    unlink($this->fileList[$i]['tmp_name']);
                }
            
        }
        foreach ($this->fileList as $item) {
            if (!empty($item['error'])) {
                return false;
            }
        }
        return true;
    }
}
