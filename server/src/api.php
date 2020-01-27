<?php
/**
* @OA\OpenApi(
*     @OA\Server(
*         url="redcrossquest.com",
*         description="RedCrossQuest API"
*     ),
*     @OA\Info(
*         version="1.0.0",
*         title="RedCrossQuest API",
*         description="API for the angular application RedCrossQuest",
*         @OA\Contact(name="cv@mansonthomas.com"),
*         @OA\License(name="GPLv3")
*     ),
* )
*/

/**
* @OA\Schema(
*     schema="ErrorModel",
*     required={"code", "message"},
*     @OA\Property(
*         property="code",
*         type="integer",
*         format="int32"
*     ),
*     @OA\Property(
*         property="message",
*         type="string"
*     )
* )
*/
