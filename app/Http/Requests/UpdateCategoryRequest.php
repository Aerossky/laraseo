<?php

namespace App\Http\Requests;

/**
 * Shares all rules with StoreCategoryRequest. The unique slug rule already
 * ignores the bound {category} route parameter.
 */
class UpdateCategoryRequest extends StoreCategoryRequest {}
