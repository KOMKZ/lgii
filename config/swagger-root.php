<?php
/**
 * @root
 * - host 47.106.36.175:8099
 * - version 1.0.0
 * - title lshop-swagger
 * - description lshop-swagger
 * - Contact name="lshop Technical Service (Shenzhen)Ltd.", url="http://www.lshop.cn"
 * - License name="lshop Technical Service (Shenzhen)Ltd.", url="http://www.lshop.cn"
 * - basePath /
 * - schemes http
 *
 */

/**
 * @def #global_res
 * - code integer,错误编号
 * - message string,错误信息
 * - data mixed,返回信息
 */

/**
 * @SWG\Info(
 *   title="Swagger Petstore",
 *   description="A sample API that uses a petstore as an example to demonstrate features in the swagger-2.0 specification",
 *   version="1.0.0",
 *   @SWG\Contact(
 *     email="apiteam@swagger.io",
 *     name="Swagger API Team",
 *     url="http://swagger.io"
 *   ),
 *   @SWG\License(
 *     name="MIT",
 *     url="http://github.com/gruntjs/grunt/blob/master/LICENSE-MIT"
 *   ),
 *   termsOfService="http://swagger.io/terms/"
 * )
 * @SWG\Swagger(
 *   host="petstore.swagger.io",
 *   basePath="/api",
 *   schemes={"http"},
 *   produces={"application/json"},
 *   consumes={"application/json"},
 *   @SWG\ExternalDocumentation(
 *     description="find more info here",
 *     url="https://swagger.io/about"
 *   )
 * )
 */