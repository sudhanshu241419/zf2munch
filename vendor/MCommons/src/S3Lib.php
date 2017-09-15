<?php
/**
 * Amazon s3 library wrapper for creating/upload bucket and objects on s3 server
 * 
 * @author Krunal Goswami
 *
 */
namespace MCommons;

use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\Model\ClearBucket;
use Guzzle\Http\EntityBody;

class S3Lib
{

    private $_client;

    private $_acl;

    private $_debug = 1;

    private $_base_bucket;

    const NO_CONFIG_ERROR = "s3 configurations not found.";

    const NO_BUCKET_ERROR = "bucket name or key dose not exists.";

    const INVALID_BUCKET_NAME = "bucket name is invalid.";

    const NO_KEY_ERROR = "key dose not exists.";

    const NO_FILE_ERROR = "filepath dose not exists";

    function __construct($config = array())
    {
        if (empty($config)) {
            $config = StaticOptions::getServiceLocator()->get('config');
            if (! isset($config['s3'])) {
                throw new \Exception(self::NO_CONFIG_ERROR, 400);
            }
        }
        $config = $config['s3'];
        $this->_client = S3Client::factory(array(
            'key' => $config['key'],
            'secret' => $config['secret']
        ));
        $this->_acl = $this->_getAcl($config['acl']);
        if ($this->_debug) {
            $this->_base_bucket = "munchado_test";
        } else {
            $this->_base_bucket = $config['bucket_name'];
        }
    }

    /**
     * Lists all buckets
     *
     * @return array
     */
    public function listBuckets()
    {
        $result = $this->_client->listBuckets();
        return $result['Buckets'];
    }

    /**
     * create a bucket
     *
     * @param string $bucket            
     * @throws \Exception
     * @return unknown
     */
    public function createBucket($bucket = "")
    {
        $result = array();
        if ($bucket != "") {
            // if ($this->_client->isValidBucketName($bucket)) {
            $result = $this->_client->createBucket(array(
                'Bucket' => $bucket,
                "ACL" => $this->_acl
            ));
            $this->_client->waitUntilBucketExists(array(
                'Bucket' => $bucket
            ));
            /*
             * } else { throw new \Exception(self::INVALID_BUCKET_NAME, 400); }
             */
            return $result;
        } else {
            throw new \Exception(self::NO_BUCKET_ERROR, 400);
        }
    }

    /**
     * Delete single bucket
     *
     * @param string $bucket            
     * @throws \Exception
     * @return array
     */
    public function deleteBucket($bucket = "")
    {
        try {
            if ($bucket != "") {
                $this->_clearBucket($bucket);
                $result = $this->_client->deleteBucket(array(
                    'Bucket' => $bucket
                ));
                $this->_client->waitUntilBucketNotExists(array(
                    'Bucket' => $bucket
                ));
            } else {
                throw new \Exception(self::NO_BUCKET_ERROR, 400);
            }
            return $result;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Delete more than one buckets
     *
     * @param array $buckets            
     * @throws \Exception
     * @return multitype:array
     */
    public function deleteBuckets($buckets = array())
    {
        try {
            if (! empty($buckets)) {
                $result = array();
                foreach ($buckets as $bucket) {
                    $result[] = $this->deleteBucket($bucket);
                }
                return $result;
            } else {
                throw new \Exception(self::NO_BUCKET_ERROR, 400);
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * checks bucket exists and it has a permission to access
     *
     * @param string $bucket            
     * @throws \Exception
     * @return number
     */
    public function checkBucketExists($bucket = "")
    {
        try {
            if (! empty($bucket)) {
                return $this->_client->doesBucketExist($bucket);
            } else {
                throw new \Exception(self::NO_BUCKET_ERROR, 400);
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Deletes all objects from bucket
     *
     * @param string $bucket            
     * @throws \Exception
     * @return number
     */
    public function _clearBucket($bucket = "")
    {
        try {
            if (! empty($bucket)) {
                $clear = new ClearBucket($this->_client, $bucket);
                $result = $clear->clear();
                return $result;
            } else {
                throw new \Exception(self::NO_BUCKET_ERROR, 400);
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * fetches all objects from given bucket
     *
     * @param string $bucket            
     * @throws \Exception
     * @return array
     */
    public function listAllObjects($bucket = "")
    {
        try {
            if (! empty($bucket)) {
                $iterator = $this->_client->getIterator('ListObjects', array(
                    'Bucket' => $bucket
                ));
                $objects = array();
                foreach ($iterator as $object) {
                    $objects[] = $object['Key'];
                }
                return $objects;
            } else {
                throw new \Exception(self::NO_BUCKET_ERROR, 400);
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * upload/create object
     *
     * @param string $bucket            
     * @param string $key            
     * @param string $filepath            
     * @throws \Exception
     * @return array
     */
    public function createObject($bucket = "", $key = "", $filepath = "")
    {
        try {
            if (empty($bucket)) {
                throw new \Exception(self::NO_BUCKET_ERROR, 400);
            } else {
                if (! $this->checkBucketExists($bucket)) {
                    $this->createBucket($bucket);
                }
            }
            if (empty($key)) {
                throw new \Exception(self::NO_KEY_ERROR, 400);
            }
            if (empty($filepath)) {
                throw new \Exception(self::NO_FILE_ERROR, 400);
            }
            $result = $this->_client->putObject(array(
                'Bucket' => $bucket,
                'Key' => $key,
                'Body' => EntityBody::factory(file_get_contents($filepath)), // fopen($filepath, 'r+'),
                'ACL' => CannedAcl::PUBLIC_READ,
                'ContentType' => image_type_to_mime_type(exif_imagetype($filepath))
            ));
            return $result;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Get type of acl
     *
     * @param string $acl
     *            (public_read, public_read_write, private, authenticated_read, bucket_owner_full_control, bucket_owner_read)
     * @return string
     */
    private function _getAcl($acl = "")
    {
        $resAcl = "";
        switch ($acl) {
            case "public_read":
                $resAcl = CannedAcl::PUBLIC_READ;
                break;
            case "public_read_write":
                $resAcl = CannedAcl::PUBLIC_READ_WRITE;
                break;
            case "private":
                $resAcl = CannedAcl::PRIVATE_ACCESS;
                break;
            case "authenticated_read":
                $resAcl = CannedAcl::AUTHENTICATED_READ;
                break;
            case "bucket_owner_full_control":
                $resAcl = CannedAcl::BUCKET_OWNER_FULL_CONTROL;
                break;
            case "bucket_owner_read":
                $resAcl = CannedAcl::BUCKET_OWNER_READ;
                break;
            default:
                $resAcl = CannedAcl::PUBLIC_READ;
        }
        return $resAcl;
    }
}