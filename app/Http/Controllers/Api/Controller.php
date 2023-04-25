<?php

namespace App\Http\Controllers\Api\V1\Admin;

class Controller
{
  /**
 *SWG\Swagger(
 *basePath="/api",
 *host=API_HOST,
 *Schemes=API_SCHEMES,
 *produces={"application/json"},
 *consumes={"application/json"},
 * @OA\Info(
 *      version="1.0.0",
 *      title="UNLAD LGIS",
 *      description="UNLAD LGIS API",
 *      @OA\Contact(
 *          email="darius@matulionis.lt"
 *      ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */

/**
 *  @OA\Server(
 *      url="http://localhost:8000/LGU_BACK/public/api//",
 *      description="L5 Swagger OpenApi dynamic host server"
 *  )
 *
 *  @OA\Server(
*      url="http://127.0.0.1:8000/LGU_BACK/public/api/",
 *      description="L5 Swagger OpenApi Server"
 * )
 */

 /**
 * @OA\SecurityScheme(
 *   securityScheme="api_key",
 *   type="apiKey",
 *   in="header",
 *   name="api_key"
 * )
 */

/**
 * @OA\SecurityScheme(
 *   securityScheme="petstore_auth",
 *   type="oauth2",
 *   @OA\Flow(
 *      authorizationUrl="http://petstore.swagger.io/oauth/dialog",
 *      flow="implicit",
 *      scopes={
 *         "read:pets": "read your pets",
 *         "write:pets": "modify pets in your account"
 *      }
 *   )
 * )
 */

 
/**
 * @OA\OpenApi(
 *   security={
 *     {
 *       "oauth2": {"read:oauth2"},
 *     }
 *   }
 * )
 * */
 }