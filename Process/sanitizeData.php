<?php

    function sanitizeUserInput($allUserInput) {
        $sanitized_post = [];
        foreach ($allUserInput as $key => $value) {
            // Remove leading or trailing whitespace
            $trimmedValue = trim($value);
            
            /* Skip sanitization for passwords becuase it might remain 
            visually unchanged but could internally be encoded differently, 
            causing mismatches during validation.*/
            if ($key === 'password') {
                $sanitized_post[$key] = $trimmedValue;
            } else {
                // Convert special characters into HTML entities for other fields
                $sanitized_post[$key] = filter_var($trimmedValue, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
        }
        return $sanitized_post; 
    }

