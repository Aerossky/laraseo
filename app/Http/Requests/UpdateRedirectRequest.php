<?php

namespace App\Http\Requests;

/**
 * Shares all rules with StoreRedirectRequest. The unique from_url rule already
 * ignores the bound {redirect} route parameter.
 */
class UpdateRedirectRequest extends StoreRedirectRequest {}
