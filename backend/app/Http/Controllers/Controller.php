<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="AI Tamil Status Creator API",
 *     version="1.0.0",
 *     description="API documentation for the AI Tamil Status Creator application. A comprehensive platform for creating and sharing Tamil status images with AI-powered quote generation.",
 *     @OA\Contact(
 *         email="support@tamilstatus.app",
 *         name="Tamil Status Creator Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token obtained from login/register endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="User Management",
 *     description="User profile, preferences, and account management"
 * )
 * 
 * @OA\Tag(
 *     name="Themes",
 *     description="Theme management and browsing"
 * )
 * 
 * @OA\Tag(
 *     name="Templates",
 *     description="Template browsing, favorites, and ratings"
 * )
 * 
 * @OA\Tag(
 *     name="AI Generation",
 *     description="AI-powered Tamil quote generation and image analysis"
 * )
 * 
 * @OA\Tag(
 *     name="File Upload",
 *     description="File and image upload endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Support",
 *     description="Feedback, support, and FAQ endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Admin",
 *     description="Administrative endpoints (admin access required)"
 * )
 */
abstract class Controller
{
    //
}
