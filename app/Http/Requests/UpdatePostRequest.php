<?php

namespace App\Http\Requests;

/**
 * Shares all rules and publish gates with StorePostRequest. The unique slug
 * rule already ignores the bound {post} route parameter.
 */
class UpdatePostRequest extends StorePostRequest {}
