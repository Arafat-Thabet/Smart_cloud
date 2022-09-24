<?php
namespace Smart_cloud\Authorization_Token;
require_once __DIR__ . '/Jwt/JWT.php';
require_once __DIR__ . '/Jwt/BeforeValidException.php';
require_once __DIR__ . '/Jwt/ExpiredException.php';
require_once __DIR__ . '/Jwt/SignatureInvalidException.php';
/**
 * Authorization_token
 * ----------------------------------------------------------
 * API Token Generate/Validation
 * 
 * @author: Jeevan Lal
 * @version: 0.0.1
 */

use Exception;
use Smart_cloud\Jwt\JWT\JWT as JWT;

class Authorization_Token
{
    /**
     * Token Key
     */
    protected $token_key;

    /**
     * Token algorithm
     */
    protected $token_algorithm;

    /**
     * Token Request Header Name
     */
    protected $token_header;

    /**
     * Token Expire Time
     */
    protected $token_expire_time;


    public function __construct()
    {
        include __DIR__ . './jwt_config.php';

        /**
         * Load Config Items Values 
         */
        $this->token_key        = $jwt_config['jwt_key'];
        $this->token_algorithm  = $jwt_config['jwt_algorithm'];
        $this->token_header  = $jwt_config['token_header'];
        $this->token_expire_time  = $jwt_config['token_expire_time'];
    }

    /**
     * Generate Token
     * @param: {array} data
     */
    public function generateToken($data = null)
    {
        if ($data and is_array($data)) {
            // add api time key in user array()
            $data['API_TIME'] = time();

            try {
                return JWT::encode($data, $this->token_key, $this->token_algorithm);
            } catch (Exception $e) {
                return 'Message: ' . $e->getMessage();
            }
        } else {
            return "Token Data Undefined!";
        }
    }

    /**
     * Validate Token with Header
     * @return : user information's
     */
    public function validateToken()
    {
        /**
         * Request All Headers
         */
        $headers = getallheaders();

        /**
         * Authorization Header Exists
         */
        $token_data = $this->tokenIsExist($headers);
        if ($token_data['status'] === TRUE) {
            try {
                /**
                 * Token Decode
                 */
                try {
                    $token_decode = JWT::decode($token_data['token'], $this->token_key, array($this->token_algorithm));
                } catch (Exception $e) {
                    return ['status' => FALSE, 'message' => $e->getMessage()];
                }

                if (!empty($token_decode) and is_object($token_decode)) {

                    return ['status' => TRUE, 'data' => $token_decode];
                } else {
                    return ['status' => FALSE, 'message' => 'Forbidden'];
                }
            } catch (Exception $e) {
                return ['status' => FALSE, 'message' => $e->getMessage()];
            }
        } else {
            // Authorization Header Not Found!
            return ['status' => FALSE, 'message' => $token_data['message']];
        }
    }

    /**
     * Token Header Check
     * @param: request headers
     */
    private function tokenIsExist($headers)
    {
        if (!empty($headers) and is_array($headers)) {
            foreach ($headers as $header_name => $header_value) {
                if (strtolower(trim($header_name)) == strtolower(trim($this->token_header)))
                    return ['status' => TRUE, 'token' => $header_value];
            }
        }
        return ['status' => FALSE, 'message' => 'Token is not defined.'];
    }
}
